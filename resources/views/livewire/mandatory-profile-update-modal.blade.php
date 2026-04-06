@if($shouldShowModal)
<div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-2xl shadow-2xl p-6 sm:p-8 transform transition-all">
        
        <div class="mb-6">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 mb-4 text-primary-600 dark:text-primary-400">
                <x-heroicon-o-shield-check class="w-6 h-6" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Autentikasi Diperlukan</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Karena ini adalah login pertama Anda (atau Anda menggunakan akun default), silakan perbarui <strong>Nama</strong>, <strong>Username</strong>, dan ganti <strong>Password</strong> default Anda untuk keamanan.
            </p>
        </div>

        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="mt-8 flex justify-end">
                <x-filament::button type="submit" color="primary" size="lg" class="w-full sm:w-auto">
                    Simpan & Lanjutkan
                </x-filament::button>
            </div>
        </form>
    </div>
</div>
@endif
