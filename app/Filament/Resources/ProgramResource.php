<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\RelationManagers\KegiatansRelationManager;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ActiveYear;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';

    public static function getNavigationParentItem(): ?string
    {
        return 'Master RKA';
    }
    protected static ?string $navigationLabel = 'Master Program';
    protected static ?string $modelLabel = 'Program';
    protected static ?string $pluralModelLabel = 'Program';
    protected static ?int $navigationSort = 1;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('kode_program')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50)
                ->label('Kode Program'),
            Forms\Components\TextInput::make('nama_program')
                ->required()
                ->maxLength(255)
                ->label('Nama Program'),
            Forms\Components\Hidden::make('tahun_anggaran')
                ->default(fn() => ActiveYear::get()),
        ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $activeYear = ActiveYear::get();
        return parent::getEloquentQuery()->where('tahun_anggaran', $activeYear);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_program')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Program'),
                Tables\Columns\TextColumn::make('nama_program')
                    ->searchable()
                    ->wrap()
                    ->label('Nama Program'),
                Tables\Columns\TextColumn::make('kegiatans_count')
                    ->counts('kegiatans')
                    ->label('Jumlah Kegiatan'),
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
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('lihat_kegiatan')
                    ->label('Lihat Kegiatan')
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
            KegiatansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view' => Pages\ViewProgram::route('/{record}'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
