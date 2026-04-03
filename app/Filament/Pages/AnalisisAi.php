<?php

namespace App\Filament\Pages;

use App\Services\Ai\ContextBuilder;
use App\Services\Ai\GeminiService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\User;

class AnalisisAi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Analisis Cerdas AI';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.analisis-ai';

    public static function getNavigationBadge(): ?string
    {
        return 'AI';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public string $query = '';
    public array $chatHistory = [];
    public array $suggestions = [];
    public bool $isAnalyzing = false;
    public string $aiStatus = 'checking'; // checking, connected, disconnected
    public int $cooldownSeconds = 0;
    public string $activeModelName = 'Memuat...';

    public function mount(): void
    {
        $this->checkConnection();
        $gemini = new GeminiService();
        $this->activeModelName = $gemini->getActiveModelName();
        $this->chatHistory[] = [
            'role' => 'assistant',
            'content' => "Halo " . auth()->user()->name . "! Saya adalah **Kencana AI**, asisten cerdas Anda. Saya telah memproses snapshot data keuangan terbaru Anda.\n\nApa yang bisa saya bantu analisis hari ini?"
        ];
        $this->generateSuggestions();
    }

    public function generateSuggestions(): void
    {
        $this->suggestions = [
            "Bagaimana ringkasan penyerapan saat ini?",
            "Tampilkan 5 sub-kegiatan dengan penyerapan terendah.",
            "Analisis tren realisasi 3 bulan terakhir.",
            "Cek sisa saldo kas (SP2D) kita.",
        ];
    }

    public function askAi(?string $customQuery = null): void
    {
        $payload = $customQuery ?? $this->query;

        if (empty($payload) || strlen($payload) < 3) {
            return;
        }

        // Check rate limit before proceeding
        $gemini = new GeminiService();
        $cooldown = $gemini->getRateLimitCooldown();
        if ($cooldown > 0) {
            $this->cooldownSeconds = $cooldown;
            Notification::make()
                ->title("Harap tunggu {$cooldown} detik")
                ->body('Batas penggunaan API tercapai. Tunggu sebentar sebelum bertanya lagi.')
                ->warning()
                ->send();
            return;
        }

        $this->query = '';
        $this->chatHistory[] = ['role' => 'user', 'content' => $payload];
        $this->isAnalyzing = true;
        
        // Immediate scroll to bottom for user message
        $this->dispatch('post-analyze');

        // Perform analysis
        try {
            $contextBuilder = new ContextBuilder();
            $snapshot = $contextBuilder->getSnapshot();
            
            $response = $gemini->ask($payload, $snapshot);

            $this->chatHistory[] = ['role' => 'assistant', 'content' => $response];
            
            // Update cooldown and active model after request
            $this->cooldownSeconds = $gemini->getRateLimitCooldown();
            $this->activeModelName = $gemini->getActiveModelName();
            
            $this->dispatch('post-analyze');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AnalisisAi Exception: ' . $e->getMessage());
            Notification::make()->title('Gagal melakukan analisis')->danger()->send();
            $this->chatHistory[] = ['role' => 'assistant', 'content' => "Maaf, terjadi gangguan teknis. Pastikan koneksi internet stabil dan API Key sudah benar."];
        }

        $this->isAnalyzing = false;
    }

    public function useSuggestion(string $suggestion): void
    {
        $this->askAi($suggestion);
    }

    public function checkConnection(): void
    {
        $gemini = new GeminiService();
        // Uses cached connection check to avoid wasting quota
        $this->aiStatus = $gemini->isConnected() ? 'connected' : 'disconnected';
    }

    public function forceCheckConnection(): void
    {
        $gemini = new GeminiService();
        $this->aiStatus = $gemini->forceCheckConnection() ? 'connected' : 'disconnected';
        Notification::make()->title('Koneksi Dicek Ulang')->success()->send();
    }

    public function refreshSnapshot(): void
    {
        (new ContextBuilder())->refresh();
        // Don't re-check connection here — it wastes quota
        Notification::make()->title('Data Keuangan Disegarkan')->success()->send();
    }

    public function quickAnalysis(string $type): void
    {
        $prompts = [
            'summary' => "Berikan ringkasan eksekutif penyerapan anggaran saat ini dan bandingkan dengan ketersediaan kas.",
            'low_absorption' => "Tampilkan daftar sub-kegiatan dengan anggaran besar namun penyerapan rendah, sertakan persentasenya.",
            'trend' => "Analisis tren realisasi bulanan kita. Apakah ada akselerasi atau perlambatan?",
        ];

        if (isset($prompts[$type])) {
            $this->askAi($prompts[$type]);
        }
    }

    public function clearChat(): void
    {
        $this->chatHistory = [];
        $this->mount();
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
