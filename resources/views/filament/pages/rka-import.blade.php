<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Panduan Impor
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p>Silakan unduh template Excel/CSV di bawah ini untuk memastikan format data Anda sesuai dengan sistem.
                    Sistem akan mencocokkan kode program dan rekening untuk melakukan pembaruan (update) jika data sudah
                    ada, atau menambah data baru jika belum tersedia.</p>

                <ul class="list-disc list-inside mt-2">
                    <li><strong>Full Import:</strong> Gunakan tahun baru untuk mengimpor seluruh struktur anggaran tahun
                        tersebut.</li>
                    <li><strong>Partial/Revision:</strong> Unggah file dengan kode yang sama; sistem akan memperbarui
                        nilai pagu atau nama detail belanja.</li>
                </ul>

                <div class="mt-4 flex flex-wrap gap-4">
                    <x-filament::button wire:click="downloadTemplate" icon="heroicon-o-document-arrow-down"
                        color="gray">
                        Unduh Template (.xlsx)
                    </x-filament::button>

                    <x-filament::button wire:click="export" icon="heroicon-o-arrow-down-tray"
                        color="info">
                        Ekspor Data RKA Saat Ini
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <x-filament-panels::form wire:submit="import">
            {{ $this->form }}

            <div class="flex justify-end mt-4">
                <x-filament::button type="submit" size="lg" icon="heroicon-o-cloud-arrow-up">
                    Mulai Impor Data
                </x-filament::button>
            </div>
        </x-filament-panels::form>
    </div>
</x-filament-panels::page>