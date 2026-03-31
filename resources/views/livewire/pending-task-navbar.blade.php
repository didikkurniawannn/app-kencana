<div>
    @if($count > 0)
    <div class="pending-task-navbar-wrapper" x-data="{ open: false }" @click.away="open = false">
        <button
            @click="open = !open"
            type="button"
            class="pending-task-btn"
            title="Berkas Menunggu Tindakan"
        >
            <div class="relative inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="pending-task-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span class="pending-task-badge">
                    {{ $count }}
                </span>
            </div>
        </button>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
            class="pending-task-dropdown"
            style="display: none;"
        >
            <div class="pending-task-dropdown-header">Tugas Verifikasi</div>
            <div class="py-1">
                @foreach ($details as $label => $val)
                    <div class="pending-task-item">
                        <span class="task-label">{{ $label }}</span>
                        <span class="task-count">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
            <div class="pending-task-dropdown-footer">
                Klik menu di samping untuk detail
            </div>
        </div>
    </div>
    @endif

    <style>
        .pending-task-navbar-wrapper { position: relative; margin-right: 0.5rem; display: flex; align-items: center; }
        .pending-task-btn {
            display: flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 10px;
            background: #fff; border: 1.5px solid #e2e8f0;
            color: #64748b; cursor: pointer; transition: all 0.2s;
            position: relative;
        }
        .pending-task-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #334155; }
        .pending-task-icon { color: #f59e0b; }
        .pending-task-badge {
            position: absolute; top: -6px; right: -6px;
            background: #ef4444; color: white; border-radius: 9999px;
            min-width: 18px; height: 18px; padding: 0 4px;
            font-size: 10px; font-weight: 800; border: 2px solid #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            z-index: 10;
        }
        .pending-task-dropdown {
            position: absolute; top: calc(100% + 6px); right: 0;
            min-width: 220px; background: #fff; border: 1px solid #e2e8f0;
            border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            z-index: 100; overflow: hidden;
        }
        .pending-task-dropdown-header {
            padding: 0.6rem 0.85rem; font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
        }
        .pending-task-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.6rem 0.85rem; border-bottom: 1px solid #f8fafc;
        }
        .task-label { font-size: 0.75rem; font-weight: 500; color: #475569; }
        .task-count { 
            padding: 0.1rem 0.5rem; font-size: 10px; font-weight: 700; 
            background: #fef3c7; color: #d97706; border-radius: 9999px;
        }
        .pending-task-dropdown-footer {
            padding: 0.5rem 0.85rem; font-size: 0.65rem; color: #94a3b8;
            background: #fefce8; text-align: center; font-style: italic; border-top: 1px solid #fef9c3;
        }
        
        .dark .pending-task-btn { background: #1e293b; border-color: #334155; color: #94a3b8; }
        .dark .pending-task-dropdown { background: #1e293b; border-color: #334155; }
        .dark .pending-task-dropdown-header { background: #0f172a; color: #64748b; border-color: #334155; }
        .dark .task-label { color: #cbd5e1; }
        .dark .task-count { background: #451a03; color: #fbbf24; }
        .dark .pending-task-dropdown-footer { background: #0f172a; color: #64748b; border-color: #334155; }
    </style>
</div>
