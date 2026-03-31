<x-filament-panels::page>
    {{-- Tab Navigation --}}
    <div class="bku-tabs flex gap-3 mb-6">
        <button
            wire:click="switchTab('semua')"
            class="bku-tab {{ $activeTab === 'semua' ? 'bku-tab-active' : '' }}"
        >
            <x-heroicon-o-banknotes class="w-6 h-6" />
            <span>Semua</span>
        </button>
        <button
            wire:click="switchTab('non-pegawai')"
            class="bku-tab {{ $activeTab === 'non-pegawai' ? 'bku-tab-active' : '' }}"
        >
            <x-heroicon-o-shopping-cart class="w-6 h-6" />
            <span>Non Pegawai</span>
        </button>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Bulan</label>
                <select wire:model.live="filterBulan" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="">Semua Bulan</option>
                    @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Triwulan</label>
                <select wire:model.live="filterTriwulan" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="">Semua Triwulan</option>
                    <option value="1">Triwulan I (Jan-Mar)</option>
                    <option value="2">Triwulan II (Apr-Jun)</option>
                    <option value="3">Triwulan III (Jul-Sep)</option>
                    <option value="4">Triwulan IV (Okt-Des)</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Dari Tanggal</label>
                <input type="date" wire:model.live="filterFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Sampai Tanggal</label>
                <input type="date" wire:model.live="filterUntil" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
            </div>
            <div>
                <button wire:click="resetFilters" class="w-full px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 transition">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    @php
        $summary = $this->getSummary();
        $data = $this->getBkuData();
        $pctUsed = $summary['total_masuk'] > 0 ? round(($summary['total_keluar'] / $summary['total_masuk']) * 100, 1) : 0;
    @endphp

    {{-- Summary Section - Premium Design --}}
    <div class="bku-summary-grid mb-6">
        {{-- Main Card: Masuk vs Keluar --}}
        <div class="bku-summary-main">
            <div class="bku-summary-main-inner">
                {{-- Left: Masuk --}}
                <div class="bku-summary-col">
                    <div class="bku-summary-icon bku-icon-masuk">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75" />
                        </svg>
                    </div>
                    <div>
                        <span class="bku-summary-label">Total Uang Masuk</span>
                        <span class="bku-summary-value bku-val-masuk">Rp {{ number_format($summary['total_masuk'], 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="bku-summary-divider"></div>

                {{-- Right: Keluar --}}
                <div class="bku-summary-col">
                    <div class="bku-summary-icon bku-icon-keluar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" />
                        </svg>
                    </div>
                    <div>
                        <span class="bku-summary-label">Total Uang Keluar</span>
                        <span class="bku-summary-value bku-val-keluar">Rp {{ number_format($summary['total_keluar'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="bku-progress-wrap">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-gray-500 dark:text-gray-400">Penggunaan Dana</span>
                    <span class="font-semibold {{ $pctUsed > 90 ? 'text-red-500' : ($pctUsed > 70 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400') }}">{{ $pctUsed }}%</span>
                </div>
                <div class="bku-progress-bar">
                    <div class="bku-progress-fill {{ $pctUsed > 90 ? 'bku-fill-danger' : ($pctUsed > 70 ? 'bku-fill-warning' : 'bku-fill-safe') }}" style="width: {{ min($pctUsed, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Saldo Card --}}
        <div class="bku-summary-saldo {{ $summary['saldo_akhir'] >= 0 ? 'bku-saldo-positive' : 'bku-saldo-negative' }}">
            <div class="bku-saldo-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                </svg>
            </div>
            <span class="bku-saldo-label">Saldo Akhir</span>
            <span class="bku-saldo-value">Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}</span>
        </div>

        {{-- Transaksi Card --}}
        <div class="bku-summary-transaksi">
            <div class="bku-transaksi-counts">
                <div class="bku-transaksi-item">
                    <span class="bku-transaksi-num bku-num-masuk">{{ $data->where('tipe', 'masuk')->count() }}</span>
                    <span class="bku-transaksi-sub">Masuk</span>
                </div>
                <div class="bku-transaksi-sep"></div>
                <div class="bku-transaksi-item">
                    <span class="bku-transaksi-num bku-num-keluar">{{ $data->where('tipe', 'keluar')->count() }}</span>
                    <span class="bku-transaksi-sub">Keluar</span>
                </div>
            </div>
            <div class="bku-transaksi-total">
                <span class="bku-transaksi-total-num">{{ $summary['jumlah_transaksi'] }}</span>
                <span class="bku-transaksi-total-label">Total Transaksi</span>
            </div>
        </div>
    </div>

    {{-- BKU Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Uraian</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">No. Bukti</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Uang Masuk</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Uang Keluar</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($data as $index => $entry)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors {{ $entry['tipe'] === 'masuk' ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}">
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                {{ $entry['tanggal'] ? $entry['tanggal']->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-800 dark:text-gray-200 max-w-xs">
                                <div class="flex items-center gap-2">
                                    @if($entry['tipe'] === 'masuk')
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30">MASUK</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30">KELUAR</span>
                                    @endif
                                    <span class="truncate">{{ $entry['uraian'] }}</span>
                                </div>
                                @if($entry['expense_type'])
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $entry['expense_type'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">{{ $entry['nomor_bukti'] }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs max-w-[200px] truncate" title="{{ $entry['kegiatan'] }}">
                                {{ $entry['kegiatan'] }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono {{ $entry['uang_masuk'] > 0 ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-300 dark:text-gray-600' }}">
                                {{ $entry['uang_masuk'] > 0 ? 'Rp ' . number_format($entry['uang_masuk'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono {{ $entry['uang_keluar'] > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-300 dark:text-gray-600' }}">
                                {{ $entry['uang_keluar'] > 0 ? 'Rp ' . number_format($entry['uang_keluar'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold {{ $entry['saldo'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($entry['saldo'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                                <p class="text-sm font-medium">Belum ada data BKU</p>
                                <p class="text-xs">Data akan muncul setelah ada pencairan SP2D dan realisasi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($data->count() > 0)
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                        <td colspan="5" class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 uppercase text-xs tracking-wider">Total</td>
                        <td class="px-4 py-3 text-right font-mono text-blue-700 dark:text-blue-300">
                            Rp {{ number_format($data->sum('uang_masuk'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-red-700 dark:text-red-300">
                            Rp {{ number_format($data->sum('uang_keluar'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-emerald-700 dark:text-emerald-300">
                            Rp {{ number_format($data->last()['saldo'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <style>
        /* ===== BKU Tab Styles ===== */
        .bku-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.85rem 1.25rem;
            min-width: 100px;
            border: 2px dashed #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            transition: all 0.25s ease;
            gap: 0.4rem;
            cursor: pointer;
            position: relative;
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
        }
        .dark .bku-tab { background: #1e293b; border-color: #334155; color: #94a3b8; }
        .bku-tab:hover { border-color: #cbd5e1; background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .dark .bku-tab:hover { background: #334155; border-color: #475569; }
        .bku-tab-active { border: 2px solid #e2e8f0 !important; background: #ffffff !important; color: #1e293b !important; font-weight: 600 !important; box-shadow: 0 4px 12px rgba(59,130,246,0.1) !important; }
        .bku-tab-active::after { content: ""; position: absolute; bottom: -2px; left: 20%; right: 20%; height: 3px; background: #3b82f6; border-radius: 3px 3px 0 0; }
        .bku-tab-active svg { color: #3b82f6 !important; }
        .dark .bku-tab-active { background: #1e293b !important; border-color: #475569 !important; color: #f1f5f9 !important; }
        .dark .bku-tab-active svg { color: #60a5fa !important; }

        /* ===== Summary Grid ===== */
        .bku-summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .bku-summary-grid {
                grid-template-columns: 2fr 1fr 1fr;
            }
        }

        /* Main Card: Masuk vs Keluar */
        .bku-summary-main {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .dark .bku-summary-main { background: #1e293b; box-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.06); }

        .bku-summary-main-inner {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .bku-summary-col {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .bku-summary-col > div:last-child {
            display: flex;
            flex-direction: column;
        }
        .bku-summary-divider {
            width: 1px;
            height: 48px;
            background: #e5e7eb;
            flex-shrink: 0;
        }
        .dark .bku-summary-divider { background: #374151; }

        .bku-summary-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .bku-icon-masuk { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }
        .dark .bku-icon-masuk { background: linear-gradient(135deg, #1e3a5f, #1e40af33); color: #60a5fa; }
        .bku-icon-keluar { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
        .dark .bku-icon-keluar { background: linear-gradient(135deg, #5f1e1e, #991b1b33); color: #f87171; }

        .bku-summary-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            font-weight: 600;
        }
        .dark .bku-summary-label { color: #6b7280; }

        .bku-summary-value {
            font-size: 1.15rem;
            font-weight: 700;
            font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, monospace;
            line-height: 1.3;
        }
        .bku-val-masuk { color: #2563eb; }
        .dark .bku-val-masuk { color: #60a5fa; }
        .bku-val-keluar { color: #dc2626; }
        .dark .bku-val-keluar { color: #f87171; }

        /* Progress Bar */
        .bku-progress-wrap { padding-top: 0.25rem; }
        .bku-progress-bar {
            height: 8px;
            background: #f3f4f6;
            border-radius: 99px;
            overflow: hidden;
        }
        .dark .bku-progress-bar { background: #374151; }
        .bku-progress-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.8s ease;
        }
        .bku-fill-safe { background: linear-gradient(90deg, #34d399, #10b981); }
        .bku-fill-warning { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
        .bku-fill-danger { background: linear-gradient(90deg, #f87171, #ef4444); }

        /* Saldo Card */
        .bku-summary-saldo {
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        .bku-saldo-positive {
            background: linear-gradient(145deg, #ecfdf5, #d1fae5);
            box-shadow: 0 1px 3px rgba(16,185,129,0.12), 0 0 0 1px rgba(16,185,129,0.08);
        }
        .dark .bku-saldo-positive {
            background: linear-gradient(145deg, #064e3b, #065f46);
            box-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 0 0 1px rgba(16,185,129,0.15);
        }
        .bku-saldo-negative {
            background: linear-gradient(145deg, #fef2f2, #fee2e2);
            box-shadow: 0 1px 3px rgba(239,68,68,0.12), 0 0 0 1px rgba(239,68,68,0.08);
        }
        .dark .bku-saldo-negative {
            background: linear-gradient(145deg, #450a0a, #7f1d1d);
            box-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 0 0 1px rgba(239,68,68,0.15);
        }
        .bku-saldo-icon-wrap {
            width: 48px; height: 48px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.6);
        }
        .bku-saldo-positive .bku-saldo-icon-wrap { color: #059669; }
        .bku-saldo-negative .bku-saldo-icon-wrap { color: #dc2626; }
        .dark .bku-saldo-icon-wrap { background: rgba(255,255,255,0.08); }
        .dark .bku-saldo-positive .bku-saldo-icon-wrap { color: #34d399; }
        .dark .bku-saldo-negative .bku-saldo-icon-wrap { color: #f87171; }

        .bku-saldo-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            color: #6b7280;
        }
        .dark .bku-saldo-label { color: #9ca3af; }

        .bku-saldo-value {
            font-size: 1.35rem;
            font-weight: 800;
            font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, monospace;
        }
        .bku-saldo-positive .bku-saldo-value { color: #047857; }
        .bku-saldo-negative .bku-saldo-value { color: #b91c1c; }
        .dark .bku-saldo-positive .bku-saldo-value { color: #6ee7b7; }
        .dark .bku-saldo-negative .bku-saldo-value { color: #fca5a5; }

        /* Transaksi Card */
        .bku-summary-transaksi {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1rem;
        }
        .dark .bku-summary-transaksi { background: #1e293b; box-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.06); }

        .bku-transaksi-counts {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
        }
        .bku-transaksi-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .bku-transaksi-num {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }
        .bku-num-masuk { color: #2563eb; }
        .dark .bku-num-masuk { color: #60a5fa; }
        .bku-num-keluar { color: #dc2626; }
        .dark .bku-num-keluar { color: #f87171; }

        .bku-transaksi-sub {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            font-weight: 600;
            margin-top: 2px;
        }
        .dark .bku-transaksi-sub { color: #6b7280; }

        .bku-transaksi-sep {
            width: 1px;
            height: 32px;
            background: #e5e7eb;
        }
        .dark .bku-transaksi-sep { background: #374151; }

        .bku-transaksi-total {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }
        .dark .bku-transaksi-total { border-top-color: #374151; }

        .bku-transaksi-total-num {
            font-size: 1.1rem;
            font-weight: 800;
            color: #7c3aed;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dark .bku-transaksi-total-num {
            color: #a78bfa;
            background: linear-gradient(135deg, #4c1d95, #5b21b633);
        }
        .bku-transaksi-total-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
        }
        .dark .bku-transaksi-total-label { color: #9ca3af; }
    </style>
</x-filament-panels::page>
