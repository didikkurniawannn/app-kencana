<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
                    <div class="flex flex-col">
                        <span class="text-base font-bold">Pemantauan Khusus Koefisien per Rekening Belanja</span>
                        <span class="text-sm text-gray-500 mt-1">
                            Membandingkan serapan jumlah volume / besaran koefisien yang dicadangkan versus yang direalisasikan.
                        </span>
                    </div>
                </div>
            </x-slot>

            <div class="mt-4">
                {{ $this->table }}
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
