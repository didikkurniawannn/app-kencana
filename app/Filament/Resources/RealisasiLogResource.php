<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RealisasiLogResource\Pages;
use App\Models\RealisasiLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RealisasiLogResource extends Resource
{
    protected static ?string $model = RealisasiLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Log Tiket (Histori)';
    protected static ?string $modelLabel = 'Log Tiket';
    protected static ?int $navigationSort = 10;
    protected static ?string $tenantOwnershipRelationshipName = 'realisasi.instansi';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->latest();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('realisasi.nomor_register')
                    ->label('No. Register')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Aktor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Tindakan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'diajukan' => 'warning',
                        'verifikasi' => 'info',
                        'disetujui' => 'success',
                        'ditolak', 'dikembalikan' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan/Alasan')
                    ->wrap()
                    ->searchable()
                    ->limit(100),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'diajukan' => 'Pengajuan',
                        'verifikasi' => 'Verifikasi',
                        'disetujui' => 'Persetujuan',
                        'dikembalikan' => 'Pengembalian',
                        'ditolak' => 'Penolakan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRealisasiLogs::route('/'),
        ];
    }
}
