<div class="space-y-4 max-h-[60vh] overflow-y-auto">
    <table class="w-full text-sm text-left border rounded-lg overflow-hidden whitespace-nowrap md:whitespace-normal">
        <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0">
            <tr>
                <th class="p-3 font-semibold dark:text-gray-200">Asal Sub Kegiatan</th>
                <th class="p-3 font-semibold dark:text-gray-200">Rincian Belanja</th>
                <th class="p-3 font-semibold text-center dark:text-gray-200">Volume</th>
                <th class="p-3 font-semibold text-right dark:text-gray-200">Harga Satuan (Rp)</th>
                <th class="p-3 font-semibold text-right dark:text-gray-200">Total Pagu (Rp)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($details as $det)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="p-3 text-xs text-gray-500 dark:text-gray-400">
                        @if($det->rekening && $det->rekening->subKegiatan)
                            {{ $det->rekening->subKegiatan->nama_sub_kegiatan }}
                        @else
                            <span class="italic">Tidak terikat</span>
                        @endif
                    </td>
                    <td class="p-3 font-medium dark:text-gray-300 min-w-[200px]">
                        {{ $det->nama_detail_belanja }}
                    </td>
                    <td class="p-3 text-center text-gray-700 dark:text-gray-300">
                        {{ (float) ($det->kuefisien_murni ?? $det->kuefisien) }}
                    </td>
                    <td class="p-3 text-right text-gray-700 dark:text-gray-300">
                        {{ number_format((float)$det->harga, 0, ',', '.') }}
                    </td>
                    <td class="p-3 text-right text-emerald-600 dark:text-emerald-400 font-bold">
                        {{ number_format((float)$det->pagu, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-3 text-center text-gray-500">Tidak ada rincian belanja untuk rekening ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="text-xs text-gray-500 dark:text-gray-400 italic mt-2">
        *Daftar di atas adalah seluruh "Detail Belanja" yang menyusun total Pagu pada grup Jenis Rekening ini.
    </div>
</div>
