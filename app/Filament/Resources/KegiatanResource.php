<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanResource\Pages;
use App\Filament\Resources\KegiatanResource\RelationManagers\SubKegiatansRelationManager;
use App\Models\Kegiatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Helpers\ActiveYear;

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';

    public static function getNavigationParentItem(): ?string
    {
        return 'Master RKA';
    }
    protected static ?string $navigationLabel = 'Master Kegiatan';
    protected static ?string $modelLabel = 'Kegiatan';
    protected static ?string $pluralModelLabel = 'Kegiatan';
    protected static ?int $navigationSort = 2;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_kegiatan')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->label('Kode Kegiatan'),
                Forms\Components\TextInput::make('nama_kegiatan')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Kegiatan'),
                Forms\Components\Select::make('program_id')
                    ->relationship('program', 'nama_program', function ($query) {
                        $activeYear = ActiveYear::get();
                        return $query->where('tahun_anggaran', $activeYear);
                    })
                    ->searchable()
                    ->preload()
                    ->label('Program')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_kegiatan')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Kegiatan'),
                Tables\Columns\TextColumn::make('nama_kegiatan')
                    ->searchable()
                    ->wrap()
                    ->label('Nama Kegiatan'),
                Tables\Columns\TextColumn::make('program.nama_program')
                    ->searchable()
                    ->wrap()
                    ->label('Program'),
                Tables\Columns\TextColumn::make('sub_kegiatans_count')
                    ->counts('subKegiatans')
                    ->label('Jumlah Sub Kegiatan'),
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
                Tables\Actions\Action::make('lihat_sub_kegiatan')
                    ->label('Lihat Sub Kegiatan')
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
            SubKegiatansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKegiatans::route('/'),
            'create' => Pages\CreateKegiatan::route('/create'),
            'view' => Pages\ViewKegiatan::route('/{record}'),
            'edit' => Pages\EditKegiatan::route('/{record}/edit'),
        ];
    }
}
