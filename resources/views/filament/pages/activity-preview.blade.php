<div class="space-y-4">
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800">
                    @foreach($logs[0] ?? [] as $header)
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach(array_slice($logs, 1) as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        @foreach($row as $index => $cell)
                            <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-400">
                                @if($index === 8) {{-- JSON column --}}
                                    <div class="max-w-[300px] overflow-hidden text-ellipsis whitespace-pre-wrap font-mono text-[10px] bg-gray-50 dark:bg-gray-800 p-1 rounded border border-gray-100 dark:border-gray-700">
                                        {{ $cell }}
                                    </div>
                                @else
                                    {{ $cell }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="flex items-center justify-between text-xs text-gray-500">
        <p>Menampilkan {{ count($logs) - 1 }} baris dari memori.</p>
        <p class="italic text-amber-600 dark:text-amber-400 underline decoration-dotted">Data ini akan terhapus dari memori dalam 10 menit sejak pemuatan.</p>
    </div>
</div>
