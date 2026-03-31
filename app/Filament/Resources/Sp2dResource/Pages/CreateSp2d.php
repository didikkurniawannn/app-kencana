<?php

namespace App\Filament\Resources\Sp2dResource\Pages;

use App\Filament\Resources\Sp2dResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class CreateSp2d extends CreateRecord
{
    protected static string $resource = Sp2dResource::class;

    protected function afterCreate(): void
    {
        $sp2d = $this->record;
        $tenantId = $sp2d->instansi_id;

        $verifikators = User::byRoleAndTenant('verifikator', $tenantId)->get();

        Notification::make()
            ->title('Sumber Dana Baru Diinput')
            ->body("Nomor SPM: {$sp2d->nomor_sp2d} telah diinput oleh " . auth()->user()->name . ". Menunggu verifikasi Anda.")
            ->icon('heroicon-o-document-currency-dollar')
            ->color('warning')
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(Sp2dResource::getUrl('index', ['tenant' => $tenantId])),
            ])
            ->sendToDatabase($verifikators);
    }
}
