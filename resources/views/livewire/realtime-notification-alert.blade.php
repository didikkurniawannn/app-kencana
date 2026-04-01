<div wire:poll.10s="checkForNewNotifications">
    {{-- Toast Popup for New Notifications --}}
    <div
        x-data="{
            show: false,
            title: '',
            body: '',
            timeout: null,
            init() {
                Livewire.on('new-notification-received', (data) => {
                    const payload = Array.isArray(data) ? data[0] : data;
                    this.title = payload.title || 'Notifikasi Baru';
                    this.body = payload.body || '';
                    this.show = true;
                    this.playSound();
                    
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(() => { this.show = false; }, 8000);
                });
            },
            playSound() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    
                    // Play a pleasant two-tone notification chime
                    const playTone = (freq, start, duration) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.value = freq;
                        gain.gain.setValueAtTime(0.15, ctx.currentTime + start);
                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + start + duration);
                        osc.start(ctx.currentTime + start);
                        osc.stop(ctx.currentTime + start + duration);
                    };
                    
                    playTone(523.25, 0, 0.15);    // C5
                    playTone(659.25, 0.12, 0.15);  // E5
                    playTone(783.99, 0.24, 0.25);  // G5
                } catch (e) {
                    // AudioContext not available
                }
            },
            dismiss() {
                this.show = false;
                clearTimeout(this.timeout);
            }
        }"
    >
        {{-- Toast Notification --}}
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-95"
            style="
                position: fixed;
                bottom: 1.5rem;
                right: 1.5rem;
                z-index: 9999;
                max-width: 380px;
                width: 100%;
                display: none;
            "
            :style="show && { display: 'block' }"
        >
            <div class="notif-toast">
                {{-- Animated gradient border --}}
                <div class="notif-toast-glow"></div>

                <div class="notif-toast-inner">
                    {{-- Icon --}}
                    <div class="notif-toast-icon-wrap">
                        <div class="notif-toast-icon-ring"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="notif-toast-icon">
                            <path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9a2.25 2.25 0 104.496 0 25.057 25.057 0 01-4.496 0z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    {{-- Content --}}
                    <div class="notif-toast-content">
                        <p class="notif-toast-title" x-text="title"></p>
                        <p class="notif-toast-body" x-show="body" x-text="body"></p>
                    </div>

                    {{-- Close --}}
                    <button @click="dismiss()" class="notif-toast-close">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Progress bar --}}
                <div class="notif-toast-progress" x-show="show">
                    <div class="notif-toast-progress-bar"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .notif-toast {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 0 30px rgba(20, 184, 166, 0.1);
        }
        .notif-toast-glow {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #14b8a6, #3b82f6, #8b5cf6, #14b8a6);
            background-size: 300% 300%;
            animation: toast-glow 3s ease infinite;
            z-index: 0;
        }
        .notif-toast-inner {
            position: relative;
            z-index: 1;
            margin: 2px;
            padding: 1rem 1.25rem;
            background: #ffffff;
            border-radius: 14px;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
        }
        .notif-toast-icon-wrap {
            position: relative;
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .notif-toast-icon-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.15), rgba(59, 130, 246, 0.15));
            animation: toast-ring-pulse 2s ease-in-out infinite;
        }
        .notif-toast-icon {
            position: relative;
            width: 24px;
            height: 24px;
            color: #14b8a6;
            animation: toast-bell-swing 0.5s ease-in-out 0.3s;
            transform-origin: top center;
        }
        .notif-toast-content {
            flex: 1;
            min-width: 0;
        }
        .notif-toast-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.15rem 0;
            line-height: 1.4;
        }
        .notif-toast-body {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .notif-toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: all 0.15s;
            margin-top: 1px;
        }
        .notif-toast-close:hover {
            background: #f1f5f9;
            color: #475569;
        }
        .notif-toast-progress {
            position: relative;
            z-index: 1;
            height: 3px;
            background: #f1f5f9;
            margin: 0 2px 2px 2px;
            border-radius: 0 0 14px 14px;
            overflow: hidden;
        }
        .notif-toast-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #14b8a6, #3b82f6);
            animation: toast-progress 8s linear forwards;
            border-radius: 0 0 14px 14px;
        }

        /* Dark mode */
        .dark .notif-toast-inner {
            background: #1e293b;
        }
        .dark .notif-toast-title {
            color: #f1f5f9;
        }
        .dark .notif-toast-body {
            color: #94a3b8;
        }
        .dark .notif-toast-close:hover {
            background: #334155;
            color: #e2e8f0;
        }
        .dark .notif-toast-progress {
            background: #334155;
        }

        @keyframes toast-glow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes toast-ring-pulse {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.15); opacity: 1; }
        }
        @keyframes toast-bell-swing {
            0% { transform: rotate(0deg); }
            20% { transform: rotate(15deg); }
            40% { transform: rotate(-10deg); }
            60% { transform: rotate(6deg); }
            80% { transform: rotate(-3deg); }
            100% { transform: rotate(0deg); }
        }
        @keyframes toast-progress {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</div>
