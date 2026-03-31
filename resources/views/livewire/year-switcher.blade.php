<div class="year-switcher-wrapper" x-data="{ open: false }" @click.away="open = false">
    <button
        @click="open = !open"
        type="button"
        class="year-switcher-btn"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="year-switcher-icon" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
        </svg>
        <span class="year-switcher-label">TA {{ $selectedYear }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="year-switcher-chevron" :class="{ 'rotated': open }" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="year-switcher-dropdown"
        style="display: none;"
    >
        <div class="year-switcher-dropdown-header">Tahun Anggaran</div>
        @foreach ($availableYears as $year)
            <button
                wire:click="switchYear({{ $year }})"
                type="button"
                class="year-switcher-option {{ $year == $selectedYear ? 'active' : '' }}"
            >
                <span>{{ $year }}</span>
                @if ($year == $selectedYear)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16" class="year-check-icon">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                @endif
            </button>
        @endforeach
    </div>
</div>
