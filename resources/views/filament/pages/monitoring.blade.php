<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Cash Flow Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">Total Uang Masuk (SP2D Aktif)</span>
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">
                        Rp {{ number_format($totalUangMasuk, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-gray-400 mt-1">Sumber dana tersedia untuk direalisasikan</span>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">Total Uang Keluar (Realisasi)</span>
                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2">
                        Rp {{ number_format($totalUangKeluar, 0, ',', '.') }}
                    </span>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        @php
                            $cashOutPercentage = $totalUangMasuk > 0 ? ($totalUangKeluar / $totalUangMasuk) * 100 : 0;
                        @endphp
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ min(100, $cashOutPercentage) }}%"></div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">Sisa Kas Instansi</span>
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-2">
                        Rp {{ number_format($sisaKas, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-gray-400 mt-1">Sisa likuiditas (Uang Masuk - Uang Keluar)</span>
                </div>
            </x-filament::section>
        </div>

        {{-- Target & Progress Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::section>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">Target Realisasi Semester Ini</span>
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-2 block">
                        {{ $targetPercentage }}%
                    </span>
                    <span class="text-xs text-gray-400 mt-1 block">Minimal realisasi yang harus dicapai dari total anggaran</span>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-500 dark:text-gray-400">Capaian Total Anggaran</span>
                    <span class="text-2xl font-bold {{ $overallPercentage >= $targetPercentage ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} mt-2 block">
                        {{ number_format($overallPercentage, 2) }}%
                    </span>
                    <span class="text-xs text-gray-400 mt-1 block">Realisasi Approved dibandingkan dengan total pagu keseluruhan</span>
                </div>
            </x-filament::section>
        </div>

        {{-- Full Hierarchy Monitoring Table --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
                    <span class="text-base font-bold">Monitoring Realisasi Berdasarkan Hirarki Anggaran</span>
                    
                    {{-- Search Input (Livewire) --}}
                    <div class="relative w-full md:max-w-xs">
                        <input wire:model.live.debounce.500ms="searchQuery" type="text" placeholder="Cari Detail / Kegiatan..."
                            class="w-full pl-4 pr-4 py-2 bg-white border border-gray-300 rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    </div>
                </div>
            </x-slot>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th class="p-3 border dark:border-gray-700">Kode & Nama Program / Kegiatan / Rekening</th>
                            <th class="p-3 border dark:border-gray-700 text-right w-40">Pagu (Rp)</th>
                            <th class="p-3 border dark:border-gray-700 text-right w-40">Realisasi (Rp)</th>
                            <th class="p-3 border dark:border-gray-700 w-48 text-center">Progress / Target ({{ $targetPercentage }}%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs as $program)
                            {{-- Program Row --}}
                            <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                                <td class="p-3 border dark:border-gray-700">PROG: {{ $program->kode_program }} - {{ $program->nama_program }}</td>
                                <td class="p-3 border dark:border-gray-700 text-right">{{ number_format($program->total_pagu, 0, ',', '.') }}</td>
                                <td class="p-3 border dark:border-gray-700 text-right">{{ number_format($program->total_realisasi, 0, ',', '.') }}</td>
                                <td class="p-3 border dark:border-gray-700 text-center">
                                    @php
                                        $percent = $program->total_pagu > 0 ? ($program->total_realisasi / $program->total_pagu) * 100 : 0;
                                        $isWarning = $percent < $targetPercentage;
                                    @endphp
                                    <div class="flex items-center space-x-2 justify-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2 dark:bg-gray-700 shrink-0">
                                            <div class="{{ $isWarning ? 'bg-amber-500' : 'bg-emerald-500' }} h-2 rounded-full"
                                                style="width: {{ min(100, $percent) }}%"></div>
                                        </div>
                                        <span class="{{ $isWarning ? 'text-amber-600' : 'text-emerald-600' }} text-xs">{{ number_format($percent, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>

                            @foreach($program->kegiatans as $kegiatan)
                                {{-- Kegiatan Row --}}
                                <tr class="italic text-gray-700 dark:text-gray-300">
                                    <td class="p-3 pl-8 border dark:border-gray-700">KEG: {{ $kegiatan->kode_kegiatan }} - {{ $kegiatan->nama_kegiatan }}</td>
                                    <td class="p-3 border dark:border-gray-700 text-right">{{ number_format($kegiatan->total_pagu, 0, ',', '.') }}</td>
                                    <td class="p-3 border dark:border-gray-700 text-right">{{ number_format($kegiatan->total_realisasi, 0, ',', '.') }}</td>
                                    <td class="p-3 border dark:border-gray-700 text-center">
                                        @php
                                            $kPercent = $kegiatan->total_pagu > 0 ? ($kegiatan->total_realisasi / $kegiatan->total_pagu) * 100 : 0;
                                        @endphp
                                        <span class="text-xs font-medium">{{ number_format($kPercent, 1) }}%</span>
                                    </td>
                                </tr>

                                @foreach($kegiatan->subKegiatans as $sub)
                                    <tr class="bg-gray-50/50 dark:bg-gray-800/30 text-xs text-gray-600 dark:text-gray-400">
                                        <td class="p-2 pl-12 border dark:border-gray-700 font-medium">SUB: {{ $sub->kode_sub_kegiatan }} - {{ $sub->nama_sub_kegiatan }}</td>
                                        <td class="p-2 border dark:border-gray-700 text-right">{{ number_format($sub->total_pagu, 0, ',', '.') }}</td>
                                        <td class="p-2 border dark:border-gray-700 text-right">{{ number_format($sub->total_realisasi, 0, ',', '.') }}</td>
                                        <td class="p-2 border dark:border-gray-700 text-center">
                                            @php
                                                $sPercent = $sub->total_pagu > 0 ? ($sub->total_realisasi / $sub->total_pagu) * 100 : 0;
                                                $sWarning = $sPercent < $targetPercentage;
                                            @endphp
                                            <div class="flex items-center space-x-1 justify-center">
                                                @if($sWarning && $sub->total_pagu > 0)
                                                    <x-heroicon-o-exclamation-circle class="w-3 h-3 text-amber-500" title="Di bawah target PMK" />
                                                @endif
                                                <span>{{ number_format($sPercent, 1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach($sub->rekenings as $rek)
                                        <tr class="text-[10px] text-gray-500 dark:text-gray-500">
                                            <td class="p-1 pl-16 border dark:border-gray-700">REK: {{ $rek->kode_rekening }} - {{ $rek->nama_rekening }}</td>
                                            <td class="p-1 border dark:border-gray-700 text-right">{{ number_format($rek->total_pagu, 0, ',', '.') }}</td>
                                            <td class="p-1 border dark:border-gray-700 text-right">{{ number_format($rek->total_realisasi, 0, ',', '.') }}</td>
                                            <td class="p-1 border dark:border-gray-700 text-center">{{ number_format($rek->total_pagu > 0 ? ($rek->total_realisasi / $rek->total_pagu) * 100 : 0, 1) }}%</td>
                                        </tr>

                                        @foreach($rek->detailBelanjas as $det)
                                            <tr class="text-[9px] text-gray-400 dark:text-gray-600">
                                                <td class="p-1 pl-20 border dark:border-gray-700 italic">• {{ $det->nama_detail_belanja }}</td>
                                                <td class="p-1 border dark:border-gray-700 text-right">{{ number_format($det->pagu, 0, ',', '.') }}</td>
                                                <td class="p-1 border dark:border-gray-700 text-right">{{ number_format($det->realisasi_total, 0, ',', '.') }}</td>
                                                <td class="p-1 border dark:border-gray-700 text-center">{{ number_format($det->pagu > 0 ? ($det->realisasi_total / $det->pagu) * 100 : 0, 1) }}%</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">Tidak ada data yang cocok dengan pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>