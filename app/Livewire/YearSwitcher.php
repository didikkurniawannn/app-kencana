<?php

namespace App\Livewire;

use App\Helpers\ActiveYear;
use Livewire\Component;

class YearSwitcher extends Component
{
    public int $selectedYear;
    public array $availableYears = [];

    public function mount(): void
    {
        $this->selectedYear = ActiveYear::get();
        $this->availableYears = ActiveYear::getAvailableYears();
    }

    public function switchYear(int $year): void
    {
        ActiveYear::set($year);
        $this->selectedYear = $year;

        // Full page reload to refresh all data
        $this->redirect(request()->header('Referer', '/admin'), navigate: true);
    }

    public function render()
    {
        return view('livewire.year-switcher');
    }
}
