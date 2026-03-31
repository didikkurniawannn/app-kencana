<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubKegiatanResource\Pages;
use App\Filament\Resources\SubKegiatanResource\RelationManagers\RekeningsRelationManager;
use App\Models\SubKegiatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Helpers\ActiveYear;

class SubKegiatanResource extends Resource
{
    protected static ?string $model = SubKegiatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';

    public static function getNavigationParentItem(): ?string
    {
        return 'Master RKA';
    }
    protected static ?string $navigationLabel = 'Master Sub Kegiatan';
    protected static ?string $modelLabel = 'Sub Kegiatan';
    protected static ?string $pluralModelLabel = 'Sub Kegiatan';
    protected static ?int $navigationSort = 3;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_sub_kegiatan')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->label('Kode Sub Kegiatan'),
                Forms\Components\TextInput::make('nama_sub_kegiatan')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Sub Kegiatan'),
                Forms\Components\Select::make('kegiatan_id')
                    ->relationship('kegiatan', 'nama_kegiatan', function ($query) {
                        $activeYear = ActiveYear::get();
                        return $query->whereHas('program', fn($q) => $q->where('tahun_anggaran', $activeYear));
                    })
                    ->searchable()
                    ->preload()
                    ->label('Kegiatan')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_sub_kegiatan')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Sub Kegiatan'),
                Tables\Columns\TextColumn::make('nama_sub_kegiatan')
                    ->searchable()
                    ->wrap()
                    ->label('Nama Sub Kegiatan'),
                Tables\Columns\TextColumn::make('kegiatan.nama_kegiatan')
                    ->searchable()
                    ->wrap()
                    ->label('Kegiatan'),
                Tables\Columns\TextColumn::make('rekenings_count')
                    ->counts('rekenings')
                    ->label('Jumlah Rekening'),
                Tables\Columns\TextColumn::make('total_pagu')
                    ->label('Total Pagu')
                    ->money('IDR')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('total_realisasi')
                    ->label('Total Realisasi')
                    ->money('IDR')
                    ->sortable(false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('lihat_detail')
                    ->label('Lihat Detail Belanja')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => self::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RekeningsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubKegiatans::route('/'),
            'create' => Pages\CreateSubKegiatan::route('/create'),
            'view' => Pages\ViewSubKegiatan::route('/{record}'),
            'edit' => Pages\EditSubKegiatan::route('/{record}/edit'),
        ];
    }
}
