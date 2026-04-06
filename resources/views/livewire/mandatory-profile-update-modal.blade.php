<div 
    x-data="{ isOpen: @entangle('shouldShowModal') }"
    x-init="if (isOpen) $dispatch('open-modal', { id: 'mandatory-profile-update' })"
>
    <x-filament::modal
        id="mandatory-profile-update"
        :close-by-clicking-away="false"
        :close-button="false"
        :esc-to-close="false"
        width="lg"
        heading="Update Akun Pertama Kali"
        description="Silakan perbarui nama dan ganti password default Anda untuk keamanan."
    >
        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end gap-x-3">
                <x-filament::button type="submit" color="primary">
                    Simpan & Lanjutkan
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>
</div>
