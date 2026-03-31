<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Illuminate\Contracts\Support\Htmlable;

class CustomDashboard extends BaseDashboard
{
    public function getTitle(): string|Htmlable
    {
        return 'Beranda';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getColumns(): int | string | array
    {
        return 12;
    }
}
