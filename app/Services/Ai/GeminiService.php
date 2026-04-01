<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1/models';

    /**
     * Models to try in order of preference.
     * Each model has its own separate quota on Free Tier,
     * so if one is exhausted, the next one likely still works.
     */
    protected array $models = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
    ];

    /**
     * Rate limit: max requests per minute (conservative for Free Tier).
     */
    protected int $maxRequestsPerMinute = 10;

    /**
     * Cache TTL for AI responses (in minutes).
     */
    protected int $responseCacheTtl = 60;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
    }

    /**
     * Get the best available model (skipping quota-exhausted ones).
     */
    protected function getAvailableModel(): ?string
    {
        foreach ($this->models as $model) {
            $blockedUntil = Cache::get("gemini_blocked_{$model}", 0);
            if (time() < $blockedUntil) {
                continue; // This model is temporarily blocked due to quota
            }
            return $model;
        }
        return null; // All models exhausted
    }

    /**
     * Mark a model as temporarily blocked (quota exhausted).
     */
    protected function blockModel(string $model, int $seconds = 120): void
    {
        Cache::put("gemini_blocked_{$model}", time() + $seconds, now()->addSeconds($seconds + 10));
    }

    /**
     * Check if the API key is configured and valid.
     * Uses cache to avoid wasting quota.
     */
    public function isConnected(): bool
    {
        if (empty($this->apiKey)) return false;

        return Cache::remember('gemini_connection_status', now()->addMinutes(10), function () {
            try {
                $response = Http::timeout(5)->get("{$this->baseUrl}?key={$this->apiKey}");
                return $response->successful();
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    /**
     * Force a fresh connection check (clears cache first).
     */
    public function forceCheckConnection(): bool
    {
        Cache::forget('gemini_connection_status');
        return $this->isConnected();
    }

    /**
     * Check if we are within client-side rate limits.
     */
    protected function isRateLimited(): bool
    {
        $requests = Cache::get('gemini_rate_counter', []);
        $now = time();
        $requests = array_filter($requests, fn($ts) => ($now - $ts) < 60);
        return count($requests) >= $this->maxRequestsPerMinute;
    }

    /**
     * Record a request for rate limiting.
     */
    protected function recordRequest(): void
    {
        $key = 'gemini_rate_counter';
        $requests = Cache::get($key, []);
        $now = time();
        $requests = array_filter($requests, fn($ts) => ($now - $ts) < 60);
        $requests[] = $now;
        Cache::put($key, array_values($requests), now()->addMinutes(2));
    }

    /**
     * Get remaining seconds until rate limit resets.
     */
    public function getRateLimitCooldown(): int
    {
        $requests = Cache::get('gemini_rate_counter', []);
        if (empty($requests)) return 0;

        $now = time();
        $requests = array_filter($requests, fn($ts) => ($now - $ts) < 60);

        if (count($requests) < $this->maxRequestsPerMinute) return 0;

        $oldest = min($requests);
        return max(0, 60 - ($now - $oldest));
    }

    /**
     * Get the name of the currently active model for display.
     */
    public function getActiveModelName(): string
    {
        $model = $this->getAvailableModel();
        if (!$model) return 'Semua model terbatas sementara';

        $labels = [
            'gemini-2.5-flash' => 'Gemini 2.5 Flash',
            'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite',
        ];

        return $labels[$model] ?? $model;
    }

    /**
     * Send a query to Gemini with financial context.
     * Implements model fallback, rate limiting, caching, and retry logic.
     */
    public function ask(string $query, array $context): string
    {
        if (empty($this->apiKey)) {
            return "Maaf, fitur Analisis AI belum dapat digunakan karena API Key belum dikonfigurasi di file .env. Silakan hubungi Administrator.";
        }

        // 1. Check client-side rate limit
        if ($this->isRateLimited()) {
            $cooldown = $this->getRateLimitCooldown();
            return "⏳ **Batas penggunaan tercapai.** Anda telah mengirim terlalu banyak pertanyaan dalam waktu singkat.\n\nSilakan tunggu **{$cooldown} detik** sebelum bertanya kembali.\n\n> 💡 **Tips:** Gunakan fitur **Analisis Cepat** di sidebar untuk pertanyaan umum — hasilnya lebih cepat karena sudah di-cache.";
        }

        // 2. Check response cache
        $cacheKey = 'gemini_resp_' . md5($query . json_encode($context));
        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse) {
            $cachedTime = Cache::get($cacheKey . '_time', 'baru-baru ini');
            return $cachedResponse . "\n\n---\n*📦 Jawaban dari cache ({$cachedTime})*";
        }

        // 3. Build the prompt
        $systemPrompt = "Anda adalah Kencana AI, asisten pakar analisis keuangan tingkat tinggi (CFO Digital) untuk Instansi Pemerintah. 
        Tujuan Anda adalah membantu Pimpinan dan Auditor memahami kesehatan keuangan secara mendalam.
        
        Anda memiliki akses ke snapshot data keuangan terpadu berikut: " . json_encode($context) . "
        
        Panduan Analisis Strategis:
        1. Gunakan Bahasa Indonesia yang sangat profesional, objektif, dan berwibawa namun tetap membantu.
        2. Analisis Data: Jangan hanya membaca angka. Gunakan data 'distribusi_rekening' untuk melihat pola pengeluaran, 'daftar_program' untuk melihat capaian unit kerja, dan 'rincian_belanja_utama' untuk menganalisis rencana strategis.
        3. Deteksi Anomali: Jika Anda melihat sisa anggaran yang besar di akhir tahun atau penyerapan yang tidak wajar pada kode rekening tertentu, sampaikan sebagai peringatan (Warning).
        4. Rekomendasi: Berikan saran pergeseran anggaran atau percepatan kegiatan jika diperlukan.
        5. Visualisasi: Gunakan Markdown (tabel, list bold) untuk membuat data kompleks menjadi sangat mudah dibaca.
        6. Akurasi: Selalu sebutkan Kode Rekening atau Nama Program yang spesifik saat memberikan analisis agar data Anda dapat ditelusuri (Traceable).";

        $requestBody = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => "Identitas & Konteks Data:\n{$systemPrompt}\n\nPertanyaan Pengguna: {$query}"]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 2048,
            ]
        ];

        // 4. Try each available model with fallback
        $triedModels = [];

        foreach ($this->models as $model) {
            // Skip models that are temporarily blocked
            $blockedUntil = Cache::get("gemini_blocked_{$model}", 0);
            if (time() < $blockedUntil) {
                $triedModels[] = "{$model} (kuota habis)";
                continue;
            }

            try {
                $this->recordRequest();

                $endpoint = "{$this->baseUrl}/{$model}:generateContent?key={$this->apiKey}";

                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpoint, $requestBody);

                if ($response->successful()) {
                    $data = $response->json();
                    $parts = $data['candidates'][0]['content']['parts'] ?? [];
                    $outputText = '';

                    foreach ($parts as $part) {
                        if (isset($part['text'])) {
                            $outputText .= $part['text'];
                        }
                    }

                    $result = $outputText ?: "Maaf, saya tidak dapat memproses jawaban saat ini.";

                    // Cache the successful response
                    Cache::put($cacheKey, $result, now()->addMinutes($this->responseCacheTtl));
                    Cache::put($cacheKey . '_time', now()->format('H:i d/m/Y'), now()->addMinutes($this->responseCacheTtl));

                    // Log which model was used
                    $labels = [
                        'gemini-2.5-flash' => 'Gemini 2.5 Flash',
                        'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite',
                        'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                        'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite',
                    ];
                    $modelLabel = $labels[$model] ?? $model;

                    return $result . "\n\n---\n*🤖 Dijawab oleh {$modelLabel}*";
                }

                if ($response->status() === 429) {
                    // This model's quota is exhausted — block it and try next
                    // Parse retry delay from response if available
                    $retryDelay = 120; // default 2 minutes
                    $body = $response->json();
                    if (isset($body['error']['details'])) {
                        foreach ($body['error']['details'] as $detail) {
                            if (($detail['@type'] ?? '') === 'type.googleapis.com/google.rpc.RetryInfo') {
                                $delayStr = $detail['retryDelay'] ?? '120s';
                                $retryDelay = (int) filter_var($delayStr, FILTER_SANITIZE_NUMBER_INT);
                                $retryDelay = max($retryDelay, 60); // at least 60s
                            }
                        }
                    }

                    $this->blockModel($model, $retryDelay);
                    $triedModels[] = "{$model} (429 → diblokir {$retryDelay}s)";
                    Log::warning("Gemini model {$model} quota exhausted, blocked for {$retryDelay}s");
                    continue; // Try next model
                }

                // Other error — log and try next model
                $triedModels[] = "{$model} (error {$response->status()})";
                Log::error("Gemini API Error ({$model}): " . $response->body());
                continue;

            } catch (\Exception $e) {
                $triedModels[] = "{$model} (exception)";
                Log::error("Gemini Service Exception ({$model}): " . $e->getMessage());
                continue;
            }
        }

        // All models failed
        Log::error('All Gemini models exhausted: ' . implode(', ', $triedModels));

        return "⚠️ **Semua model AI sedang tidak tersedia.**\n\nSemua model telah mencapai batas kuota Free Tier untuk hari ini.\n\n**Model yang dicoba:** " . implode(', ', $triedModels) . "\n\n> 💡 **Solusi:**\n> 1. Tunggu beberapa menit — kuota per-model akan pulih secara bertahap\n> 2. Pertanyaan yang sudah pernah dijawab tetap bisa diakses dari cache\n> 3. Untuk penggunaan intensif, pertimbangkan upgrade ke Paid Tier di Google AI Studio";
    }

    /**
     * Analyze a document (image/PDF) to extract archive metadata.
     * Returns structured JSON data.
     */
    public function analyzeArchiveDocument(string $filePath, string $mimeType): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception("Gemini API Key belum dikonfigurasi.");
        }

        $model = $this->getAvailableModel();
        if (!$model) {
            throw new \Exception("Layanan AI sedang sibuk (kuota habis).");
        }

        $fileContent = base64_encode(file_get_contents($filePath));

        $prompt = "Analisis dokumen kearsipan pemerintah berikut dan berikan saran lokasi penyimpanan yang paling logis.
        
        Keluarkan hasil dalam format JSON murni (tanpa markdown blok) dengan kunci berikut:
        {
            \"ruang\": \"(Saran nama unit kerja/ruang berdasarkan isi dokumen)\",
            \"box\": \"(Saran penomoran box/rak)\",
            \"sampul\": \"(Judul singkat yang sangat deskriptif untuk berkas ini)\",
            \"filing_cabinet\": \"(Saran nama lemari/kabinet)\",
            \"kode_klasifikasi\": \"(Klasifikasi ANRI yang cocok, misal: KU.01, HK.02)\"
        }

        Catatan: Gunakan data di dalam dokumen (nama instansi, perihal, tanggal) untuk menentukan jawabannya.";

        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $fileContent
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'response_mime_type' => 'application/json',
            ]
        ];

        try {
            $this->recordRequest();
            $endpoint = "{$this->baseUrl}/{$model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(60)->post($endpoint, $requestBody);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                // Clean text from potential markdown blocks if AI ignored response_mime_type
                $cleanJson = preg_replace('/```json\s*|\s*```/', '', $text);
                
                return json_decode($cleanJson, true) ?: [];
            }

            Log::error("Gemini Archive Analysis Error: " . $response->body());
            return [];

        } catch (\Exception $e) {
            Log::error("Gemini Archive Analysis Exception: " . $e->getMessage());
            return [];
        }
    }
}
