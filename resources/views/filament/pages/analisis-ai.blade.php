<x-filament-panels::page>
    <div class="gemini-chat-wrapper" x-data="{ showToolbar: false }">
        
        {{-- Top Bar --}}
        <div class="gemini-topbar">
            <div class="gemini-topbar-left">
                <div class="gemini-logo">
                    <x-heroicon-o-sparkles class="w-5 h-5" />
                </div>
                <div>
                    <h1 class="gemini-title">Kencana AI</h1>
                    <div class="gemini-model-badge">
                        <span class="gemini-status-dot {{ $aiStatus === 'connected' ? 'active' : ($aiStatus === 'checking' ? 'checking' : 'offline') }}"></span>
                        {{ $activeModelName }}
                    </div>
                </div>
            </div>
            <div class="gemini-topbar-actions">
                <button wire:click="refreshSnapshot" class="gemini-icon-btn" title="Segarkan Data Keuangan">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </button>
                <button wire:click="clearChat" class="gemini-icon-btn" title="Percakapan Baru">
                    <x-heroicon-o-plus class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div id="chat-messages" class="gemini-messages custom-scrollbar">
            
            @foreach($chatHistory as $index => $message)
                @if($message['role'] === 'user')
                    {{-- User Message --}}
                    <div class="gemini-msg-row user">
                        <div class="gemini-msg-content">
                            <div class="gemini-user-bubble">
                                {{ $message['content'] }}
                            </div>
                        </div>
                    </div>
                @else
                    {{-- AI Message --}}
                    <div class="gemini-msg-row assistant group/msg">
                        <div class="gemini-msg-content">
                            <div class="gemini-ai-avatar">
                                <x-heroicon-o-sparkles class="w-4 h-4" />
                            </div>
                            <div class="gemini-ai-body">
                                <div class="gemini-ai-text prose dark:prose-invert max-w-none">
                                    {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                </div>
                                @if($index > 0)
                                <div class="gemini-ai-actions">
                                    <button 
                                        x-data="{ copied: false }"
                                        @click="
                                            navigator.clipboard.writeText(`{{ addslashes($message['content']) }}`);
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="gemini-action-btn"
                                        title="Salin"
                                    >
                                        <x-heroicon-o-clipboard-document x-show="!copied" class="w-3.5 h-3.5" />
                                        <x-heroicon-o-check x-show="copied" class="w-3.5 h-3.5 !text-success-500" />
                                        <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Thinking Indicator --}}
            @if($isAnalyzing)
                <div class="gemini-msg-row assistant">
                    <div class="gemini-msg-content">
                        <div class="gemini-ai-avatar thinking">
                            <x-heroicon-o-sparkles class="w-4 h-4" />
                        </div>
                        <div class="gemini-ai-body">
                            <div class="gemini-thinking">
                                <div class="gemini-thinking-shimmer"></div>
                                <span>Menganalisis data keuangan...</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div id="scroll-anchor"></div>
        </div>

        {{-- Bottom Input Area --}}
        <div class="gemini-input-area">
            <div class="gemini-input-container">

                {{-- Rate Limit Cooldown --}}
                @if($cooldownSeconds > 0)
                <div x-data="{ 
                    remaining: @entangle('cooldownSeconds'),
                    interval: null,
                    init() {
                        this.interval = setInterval(() => {
                            if (this.remaining > 0) { this.remaining--; } 
                            else { clearInterval(this.interval); }
                        }, 1000);
                    },
                    destroy() { if (this.interval) clearInterval(this.interval); }
                }" class="gemini-cooldown">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Tunggu <span x-text="remaining"></span> detik</span>
                </div>
                @endif

                {{-- Quick Actions Row --}}
                @if(count($chatHistory) <= 1 && !$isAnalyzing && $cooldownSeconds <= 0)
                <div class="gemini-quick-grid">
                    <button wire:click="quickAnalysis('summary')" class="gemini-quick-card">
                        <div class="gemini-quick-icon blue">
                            <x-heroicon-o-presentation-chart-line class="w-5 h-5" />
                        </div>
                        <span>Ringkasan Eksekutif Penyerapan</span>
                    </button>
                    <button wire:click="quickAnalysis('low_absorption')" class="gemini-quick-card">
                        <div class="gemini-quick-icon amber">
                            <x-heroicon-o-arrow-trending-down class="w-5 h-5" />
                        </div>
                        <span>Deteksi Penyerapan Rendah</span>
                    </button>
                    <button wire:click="quickAnalysis('trend')" class="gemini-quick-card">
                        <div class="gemini-quick-icon green">
                            <x-heroicon-o-chart-bar-square class="w-5 h-5" />
                        </div>
                        <span>Analisis Tren Bulanan</span>
                    </button>
                    <button wire:click="useSuggestion('Cek sisa saldo kas (SP2D) kita.')" class="gemini-quick-card">
                        <div class="gemini-quick-icon purple">
                            <x-heroicon-o-banknotes class="w-5 h-5" />
                        </div>
                        <span>Cek Sisa Saldo Kas SP2D</span>
                    </button>
                </div>
                @endif

                {{-- Suggestion Chips (after conversation started) --}}
                @if(count($chatHistory) > 1 && count($suggestions) > 0 && !$isAnalyzing && $cooldownSeconds <= 0)
                <div class="gemini-suggestions">
                    @foreach($suggestions as $suggest)
                        <button wire:click="useSuggestion('{{ $suggest }}')" class="gemini-chip">
                            {{ $suggest }}
                        </button>
                    @endforeach
                </div>
                @endif

                {{-- Input Box --}}
                <form wire:submit.prevent="askAi" class="gemini-input-box">
                    <input
                        type="text"
                        wire:model="query"
                        placeholder="{{ $cooldownSeconds > 0 ? 'Menunggu cooldown...' : 'Tanyakan sesuatu tentang anggaran...' }}"
                        class="gemini-input"
                        autocomplete="off"
                        autofocus
                        {{ $isAnalyzing || $cooldownSeconds > 0 ? 'disabled' : '' }}
                    />
                    <button 
                        type="submit" 
                        class="gemini-send-btn"
                        wire:loading.attr="disabled"
                        wire:target="askAi"
                        {{ $cooldownSeconds > 0 ? 'disabled' : '' }}
                    >
                        <x-heroicon-o-arrow-up class="w-5 h-5" />
                    </button>
                </form>

                <div class="gemini-disclaimer">
                    Kencana AI dapat membuat kesalahan. Verifikasi informasi penting secara mandiri.
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ===== RESET FILAMENT PAGE DEFAULTS ===== */
        .fi-page-content-container { max-width: 100% !important; }
        .fi-page > .fi-page-content { padding: 0 !important; }
        .fi-page-header { display: none !important; }

        /* ===== PREMIUM AI SIDEBAR MENU ===== */
        .fi-sidebar-item:has(a[href*="analisis-ai"]) {
            position: relative;
            border-radius: 12px;
            padding: 2px;
            background: linear-gradient(135deg, #4285f4, #9b72cb, #d96570, #e8710a);
            background-size: 300% 300%;
            animation: ai-gradient-flow 4s ease infinite;
            margin: 4px 0;
        }
        .fi-sidebar-item:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
            background: #fff !important;
            border-radius: 10px !important;
            padding: 10px 12px !important;
            transition: all 0.3s ease;
        }
        .dark .fi-sidebar-item:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
            background: #1a1a2e !important;
        }
        .fi-sidebar-item:has(a[href*="analisis-ai"]) > a:hover {
            background: #f8f4ff !important;
        }
        .dark .fi-sidebar-item:has(a[href*="analisis-ai"]) > a:hover {
            background: #1e1e3a !important;
        }

        /* Active state */
        .fi-sidebar-item.fi-active:has(a[href*="analisis-ai"]) {
            box-shadow: 0 0 16px rgba(139, 92, 246, 0.35), 0 0 32px rgba(66, 133, 244, 0.15);
        }
        .fi-sidebar-item.fi-active:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
            background: #f0ecff !important;
        }
        .dark .fi-sidebar-item.fi-active:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
            background: #1e1a3a !important;
        }

        /* Icon gradient color */
        .fi-sidebar-item:has(a[href*="analisis-ai"]) .fi-sidebar-item-icon {
            color: #8b5cf6 !important;
            filter: drop-shadow(0 0 3px rgba(139, 92, 246, 0.4));
        }

        /* Label styling */
        .fi-sidebar-item:has(a[href*="analisis-ai"]) .fi-sidebar-item-label {
            background: linear-gradient(135deg, #4285f4, #8b5cf6, #d96570);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700 !important;
        }

        /* Badge glow */
        .fi-sidebar-item:has(a[href*="analisis-ai"]) .fi-badge {
            background: linear-gradient(135deg, #8b5cf6, #4285f4) !important;
            color: white !important;
            border: none !important;
            font-weight: 700 !important;
            font-size: 9px !important;
            letter-spacing: 0.5px;
            box-shadow: 0 0 8px rgba(139, 92, 246, 0.4);
            animation: ai-badge-pulse 2s ease-in-out infinite;
        }

        @keyframes ai-gradient-flow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes ai-badge-pulse {
            0%, 100% { box-shadow: 0 0 8px rgba(139, 92, 246, 0.4); }
            50% { box-shadow: 0 0 14px rgba(139, 92, 246, 0.7), 0 0 24px rgba(66, 133, 244, 0.3); }
        }

        /* Strip all Filament / Tailwind form styling inside chat */
        .gemini-chat-wrapper input[type="text"],
        .gemini-chat-wrapper input[type="text"]:focus,
        .gemini-chat-wrapper input[type="text"]:focus-visible {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            --tw-ring-offset-shadow: none !important;
            --tw-ring-shadow: none !important;
            --tw-shadow: none !important;
            ring: 0 !important;
        }

        /* ===== WRAPPER ===== */
        .gemini-chat-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 4rem);
            background: #ffffff;
            position: relative;
            overflow: hidden;
            z-index: 1;
            isolation: isolate;
        }
        .dark .gemini-chat-wrapper {
            background: #131314;
        }

        /* ===== TOP BAR ===== */
        .gemini-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid #e8eaed;
            background: #fff;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .dark .gemini-topbar {
            border-color: #2d2e30;
            background: #131314;
        }
        .gemini-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .gemini-logo {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a73e8 0%, #8b5cf6 50%, #e8710a 100%);
            color: white;
        }
        .gemini-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f1f1f;
            line-height: 1.2;
        }
        .dark .gemini-title { color: #e3e3e3; }
        
        .gemini-model-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            color: #70757a;
            margin-top: 2px;
        }
        .dark .gemini-model-badge { color: #9aa0a6; }
        
        .gemini-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #9aa0a6;
        }
        .gemini-status-dot.active { background: #34a853; }
        .gemini-status-dot.checking { background: #fbbc04; animation: pulse 1.5s ease infinite; }
        .gemini-status-dot.offline { background: #ea4335; }

        .gemini-topbar-actions {
            display: flex;
            gap: 4px;
        }
        .gemini-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5f6368;
            transition: all 0.15s;
            border: none;
            background: transparent;
            cursor: pointer;
        }
        .gemini-icon-btn:hover {
            background: #f1f3f4;
        }
        .dark .gemini-icon-btn { color: #9aa0a6; }
        .dark .gemini-icon-btn:hover { background: #2d2e30; }

        /* ===== MESSAGES ===== */
        .gemini-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px 0;
            scroll-behavior: smooth;
        }

        .gemini-msg-row {
            padding: 2px 0;
        }
        .gemini-msg-row.user {
            margin-bottom: 8px;
        }
        .gemini-msg-row.assistant {
            margin-bottom: 20px;
        }

        .gemini-msg-content {
            max-width: 768px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .gemini-msg-row.assistant .gemini-msg-content {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        /* User message */
        .gemini-user-bubble {
            background: #f0f4f9;
            color: #1f1f1f;
            padding: 12px 20px;
            border-radius: 24px;
            font-size: 15px;
            line-height: 1.6;
            display: inline-block;
            float: right;
            max-width: 85%;
            word-wrap: break-word;
        }
        .dark .gemini-user-bubble {
            background: #2b2d31;
            color: #e3e3e3;
        }

        /* AI Avatar */
        .gemini-ai-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a73e8 0%, #8b5cf6 50%, #e8710a 100%);
            color: white;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .gemini-ai-avatar.thinking {
            animation: avatar-pulse 2s ease-in-out infinite;
        }
        @keyframes avatar-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(0.95); }
        }

        /* AI Body */
        .gemini-ai-body {
            flex: 1;
            min-width: 0;
        }
        .gemini-ai-text {
            font-size: 15px;
            line-height: 1.75;
            color: #1f1f1f;
        }
        .dark .gemini-ai-text { color: #e3e3e3; }

        .gemini-ai-text h1, .gemini-ai-text h2, .gemini-ai-text h3 {
            margin-top: 16px;
            margin-bottom: 8px;
        }
        .gemini-ai-text p {
            margin-bottom: 12px;
        }
        .gemini-ai-text ul, .gemini-ai-text ol {
            margin-bottom: 12px;
            padding-left: 20px;
        }
        .gemini-ai-text li {
            margin-bottom: 4px;
        }
        .gemini-ai-text strong {
            font-weight: 600;
            color: #1f1f1f;
        }
        .dark .gemini-ai-text strong { color: #e3e3e3; }
        
        .gemini-ai-text table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 14px;
        }
        .gemini-ai-text th, .gemini-ai-text td {
            padding: 8px 12px;
            border: 1px solid #e8eaed;
            text-align: left;
        }
        .dark .gemini-ai-text th, .dark .gemini-ai-text td {
            border-color: #3c3f44;
        }
        .gemini-ai-text th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .dark .gemini-ai-text th { background: #2b2d31; }

        .gemini-ai-text hr {
            border: none;
            border-top: 1px solid #e8eaed;
            margin: 16px 0;
        }
        .dark .gemini-ai-text hr { border-color: #3c3f44; }
        
        .gemini-ai-text code {
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }
        .dark .gemini-ai-text code { background: #2b2d31; }

        .gemini-ai-text em {
            color: #70757a;
            font-size: 13px;
        }
        .dark .gemini-ai-text em { color: #9aa0a6; }

        /* AI Actions */
        .gemini-ai-actions {
            display: flex;
            gap: 4px;
            margin-top: 8px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .group\/msg:hover .gemini-ai-actions {
            opacity: 1;
        }
        .gemini-action-btn {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: #5f6368;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
        }
        .gemini-action-btn:hover {
            background: #f1f3f4;
            color: #1f1f1f;
        }
        .dark .gemini-action-btn { color: #9aa0a6; }
        .dark .gemini-action-btn:hover { background: #2d2e30; color: #e3e3e3; }

        /* Thinking */
        .gemini-thinking {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 0;
            font-size: 14px;
            color: #70757a;
            position: relative;
            overflow: hidden;
        }
        .dark .gemini-thinking { color: #9aa0a6; }
        
        .gemini-thinking-shimmer {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100px;
            background: linear-gradient(90deg, transparent, rgba(66,133,244,0.12), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100px); }
            100% { transform: translateX(600px); }
        }

        /* ===== BOTTOM INPUT ===== */
        .gemini-input-area {
            flex-shrink: 0;
            padding: 8px 24px 16px;
            background: #fff;
        }
        .dark .gemini-input-area { background: #131314; }

        .gemini-input-container {
            max-width: 768px;
            margin: 0 auto;
        }

        /* Cooldown */
        .gemini-cooldown {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            margin-bottom: 8px;
            border-radius: 12px;
            background: #fef7e0;
            color: #8a6d00;
            font-size: 13px;
            font-weight: 500;
        }
        .dark .gemini-cooldown { background: #3b3000; color: #fdd663; }

        /* Quick Action Grid */
        .gemini-quick-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        @media (min-width: 640px) {
            .gemini-quick-grid { grid-template-columns: repeat(4, 1fr); }
        }
        .gemini-quick-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #e3e3e3;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
            font-size: 13px;
            font-weight: 500;
            color: #3c4043;
            line-height: 1.4;
            min-height: 100px;
        }
        .gemini-quick-card:hover {
            background: #f8f9fa;
            border-color: #dadce0;
        }
        .dark .gemini-quick-card {
            border-color: #3c3f44;
            color: #bdc1c6;
        }
        .dark .gemini-quick-card:hover {
            background: #1e1f21;
            border-color: #5f6368;
        }
        .gemini-quick-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gemini-quick-icon.blue { background: #e8f0fe; color: #1a73e8; }
        .gemini-quick-icon.amber { background: #fef7e0; color: #e8710a; }
        .gemini-quick-icon.green { background: #e6f4ea; color: #1e8e3e; }
        .gemini-quick-icon.purple { background: #f3e8fd; color: #8b5cf6; }
        .dark .gemini-quick-icon.blue { background: #1a3359; color: #8ab4f8; }
        .dark .gemini-quick-icon.amber { background: #3b3000; color: #fdd663; }
        .dark .gemini-quick-icon.green { background: #0d3220; color: #81c995; }
        .dark .gemini-quick-icon.purple { background: #2d1b4e; color: #c4b5fd; }

        /* Suggestion Chips */
        .gemini-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .gemini-chip {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid #dadce0;
            background: transparent;
            font-size: 13px;
            font-weight: 500;
            color: #1a73e8;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .gemini-chip:hover {
            background: #e8f0fe;
            border-color: #1a73e8;
        }
        .dark .gemini-chip {
            border-color: #3c3f44;
            color: #8ab4f8;
        }
        .dark .gemini-chip:hover {
            background: #1a3359;
            border-color: #8ab4f8;
        }

        /* Input Box */
        .gemini-input-box {
            display: flex;
            align-items: center;
            gap: 0;
            border: 1px solid #dadce0;
            border-radius: 28px;
            padding: 4px 4px 4px 20px;
            background: #f8f9fa;
            transition: all 0.2s;
        }
        .gemini-input-box:focus-within {
            border-color: #1a73e8;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(26,115,232,0.08);
        }
        .dark .gemini-input-box {
            background: #1e1f21;
            border-color: #3c3f44;
        }
        .dark .gemini-input-box:focus-within {
            border-color: #8ab4f8;
            background: #2b2d31;
            box-shadow: 0 0 0 2px rgba(138,180,248,0.1);
        }

        .gemini-input {
            flex: 1;
            border: none !important;
            outline: none !important;
            background: transparent !important;
            font-size: 15px;
            color: #1f1f1f;
            padding: 12px 0;
            line-height: 1.5;
            box-shadow: none !important;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            ring: none;
        }
        .gemini-input:focus {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            ring: none;
            --tw-ring-shadow: none !important;
        }
        .gemini-input::placeholder {
            color: #9aa0a6;
        }
        .dark .gemini-input { color: #e3e3e3; }
        .dark .gemini-input::placeholder { color: #5f6368; }
        .gemini-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .gemini-send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1f1f1f;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .gemini-send-btn:hover { background: #3c4043; transform: scale(1.05); }
        .gemini-send-btn:active { transform: scale(0.95); }
        .gemini-send-btn:disabled { opacity: 0.3; cursor: not-allowed; transform: none; }
        .dark .gemini-send-btn { background: #8ab4f8; color: #131314; }
        .dark .gemini-send-btn:hover { background: #aecbfa; }

        /* Disclaimer */
        .gemini-disclaimer {
            text-align: center;
            font-size: 11px;
            color: #9aa0a6;
            margin-top: 10px;
            line-height: 1.4;
        }

        /* ===== SCROLLBAR ===== */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { 
            background: #dadce0; 
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #bdc1c6; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #3c3f44; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #5f6368; }

        /* ===== ANIMATIONS ===== */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Clear after user bubble */
        .gemini-msg-row.user .gemini-msg-content::after {
            content: '';
            display: table;
            clear: both;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .gemini-messages { padding: 16px 0; }
            .gemini-msg-content { padding: 0 16px; }
            .gemini-input-area { padding: 8px 12px 12px; }
            .gemini-quick-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .gemini-quick-card { padding: 12px; font-size: 12px; min-height: 80px; }
            .gemini-suggestions { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 4px; }
        }

        /* ===== PROSE OVERRIDES ===== */
        .gemini-ai-text.prose { font-size: inherit; }
        .gemini-ai-text.prose p:last-child { margin-bottom: 0; }
        .gemini-ai-text.prose :first-child { margin-top: 0; }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatWindow = document.getElementById('chat-messages');
            const scrollToBottom = () => {
                setTimeout(() => {
                    if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
                }, 100);
            };

            Livewire.on('post-analyze', () => { scrollToBottom(); });
            scrollToBottom();
        });
    </script>
</x-filament-panels::page>
