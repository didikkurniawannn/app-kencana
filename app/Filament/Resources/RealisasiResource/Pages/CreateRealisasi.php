<?php

namespace App\Filament\Resources\RealisasiResource\Pages;

use App\Filament\Resources\RealisasiResource;
use App\Models\RealisasiDetail;
use Filament\Resources\Pages\CreateRecord;

class CreateRealisasi extends CreateRecord
{
    protected static string $resource = RealisasiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        // Save dynamic fields to realisasi_details table
        $dynamicFields = $this->data['dynamic_fields'] ?? [];

        foreach ($dynamicFields as $fieldName => $fieldValue) {
            if ($fieldValue !== null && $fieldValue !== '') {
                RealisasiDetail::create([
                    'realisasi_id' => $this->record->id,
                    'field_name' => $fieldName,
                    'field_value' => is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue,
                ]);
            }
        }

        // Notify Verifikator if status is 'diajukan'
        if ($this->record->status === 'diajukan') {
            $verifikators = \App\Models\User::byRoleAndTenant('verifikator', $this->record->instansi_id)->get();
            \Filament\Notifications\Notification::make()
                ->title('Pengajuan Realisasi Baru (Input)')
                ->body("Operator " . auth()->user()->name . " telah menginput realisasi baru. Menunggu verifikasi Anda.")
                ->info()
                ->icon('heroicon-o-paper-airplane')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->button()
                        ->url(RealisasiResource::getUrl('index', ['tenant' => $this->record->instansi_id])),
                ])
                ->sendToDatabase($verifikators);
        }
    }
}
