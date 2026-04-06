<div>
    <x-filament::modal
        id="mandatory-profile-update"
        :visible="$shouldShowModal"
        :close-by-clicking-away="false"
        :close-button="false"
        :esc-to-close="false"
        width="lg"
        display-classes="block"
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

    {{-- Script untuk memastikan modal terbuka --}}
    @if($shouldShowModal)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.addEventListener('open-modal', event => {
                if(event.detail.id === 'mandatory-profile-update') {
                    // Modal already handled by :visible
                }
            });
        });
    </script>
    @endif
</div>
