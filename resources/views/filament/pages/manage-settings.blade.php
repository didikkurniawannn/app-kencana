<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button type="submit" size="lg">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>