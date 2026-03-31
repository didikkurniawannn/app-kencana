<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Breadcrumbs --}}
        <nav class="flex items-center space-x-2 text-sm text-gray-500">
            @foreach($breadcrumbs as $breadcrumb)
                <button wire:click="navigate('{{ $breadcrumb['path'] }}')"
                    class="hover:text-primary-600 focus:outline-none {{ $loop->last ? 'font-bold text-gray-900' : '' }}">
                    {{ $breadcrumb['name'] }}
                </button>
                @if(!$loop->last)
                    <span>/</span>
                @endif
            @endforeach
        </nav>

        <div class="space-y-2">
            {{-- Go Up --}}
            @if ($currentPath !== 'bukti-realisasi')
                <div wire:click="goUp"
                    class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md cursor-pointer transition-all flex items-center space-x-4 group">
                    <div
                        class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg group-hover:bg-primary-50 dark:group-hover:bg-primary-900 transition-colors">
                        <x-heroicon-o-arrow-path
                            class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-primary-600" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-gray-100 italic">.. (Parent Directory)</span>
                </div>
            @endif

            {{-- Directories --}}
            @foreach ($directories as $directory)
                <div wire:click="navigate('{{ $directory }}')"
                    class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md cursor-pointer transition-all flex items-center space-x-4 group">
                    <div
                        class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors shrink-0">
                        <x-heroicon-o-folder class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-900 dark:text-gray-100 block whitespace-normal break-words">
                            {{ $this->getDirName($directory) }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-1 shrink-0">
                        <button wire:click.stop="downloadDirectory('{{ $directory }}')"
                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                            title="Download Folder (ZIP)">
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        </button>
                        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-300 group-hover:text-gray-500 shrink-0" />
                    </div>
                </div>
            @endforeach

            {{-- Files --}}
            @foreach ($files as $file)
                <div
                    class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-all flex items-center space-x-4 group">
                    <div
                        class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 transition-colors shrink-0">
                        @php
                            $extension = pathinfo($file, PATHINFO_EXTENSION);
                            $icon = match ($extension) {
                                'pdf' => 'heroicon-o-document-text',
                                'doc', 'docx' => 'heroicon-o-document-text',
                                'xls', 'xlsx' => 'heroicon-o-table-cells',
                                'jpg', 'jpeg', 'png', 'webp' => 'heroicon-o-photo',
                                default => 'heroicon-o-document',
                            };
                        @endphp
                        <x-dynamic-component :component="$icon" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-900 dark:text-gray-100 block whitespace-normal break-words">
                            {{ $this->getFileName($file) }}
                        </span>
                        <span class="text-xs text-gray-500 block">
                            {{ strtoupper($extension) }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-1 shrink-0">
                        <a href="{{ $this->getFileUrl($file) }}" target="_blank"
                            class="p-2 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
                            title="Buka File">
                            <x-heroicon-o-eye class="w-5 h-5" />
                        </a>
                        <button wire:click="download('{{ $file }}')"
                            class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                            title="Download File">
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @if(empty($directories) && empty($files))
            <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                <x-heroicon-o-folder-open class="w-12 h-12 mb-4 opacity-20" />
                <p>Direktori ini kosong.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>