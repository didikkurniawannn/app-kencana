<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $user = auth()->user();
        $record = $this->getRecord();

        // Jika user sedang mengedit profilnya sendiri, tandai sebagai sudah diupdate
        if ($user && $record && $user->id === $record->id) {
            $record->update([
                'profile_updated_at' => now(),
            ]);
        }
    }
}
