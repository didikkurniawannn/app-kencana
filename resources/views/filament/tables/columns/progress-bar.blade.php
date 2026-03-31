<div class="fi-ta-text grid gap-y-1 px-3 py-4">
    @php
        $state = $getState();
        $percentage = (float) $state;

        // Menentukan warna berdasarkan persentase
        $colorClass = 'bg-primary-500 dark:bg-primary-400';
        if ($percentage >= 90) {
            $colorClass = 'bg-danger-500 dark:bg-danger-400';
        } elseif ($percentage >= 75) {
            $colorClass = 'bg-warning-500 dark:bg-warning-400';
        }
    @endphp

    <div class="flex items-center justify-between text-sm mb-1">
        <span class="text-gray-500 dark:text-gray-400 font-medium">Penggunaan</span>
        <span
            class="font-bold {{ $percentage >= 90 ? 'text-danger-600 dark:text-danger-400' : ($percentage >= 75 ? 'text-warning-600 dark:text-warning-400' : 'text-primary-600 dark:text-primary-400') }}">
            {{ number_format($percentage, 1) }}%
        </span>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden">
        <div class="{{ $colorClass }} h-2.5 rounded-full transition-all duration-500 ease-out"
            style="width: {{ min($percentage, 100) }}%"></div>
    </div>
</div>