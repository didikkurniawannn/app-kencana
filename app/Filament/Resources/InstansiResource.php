<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstansiResource\Pages;
use App\Filament\Resources\InstansiResource\RelationManagers;
use App\Models\Instansi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class InstansiResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Manajemen Instansi';
    protected static bool $isScopedToTenant = false;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Instansi')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->label('Kode Kecamatan')
                            ->placeholder('Misal: 32.04.28'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Instansi'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Slug/ID'),
                    ])->columns(3),
                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('instansi-logos')
                            ->disk('public')
                            ->label('Logo'),
                        Forms\Components\ColorPicker::make('primary_color')
                            ->default('#14797b')
                            ->label('Warna Tema'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->label('Logo'),
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->label('Kode Kec.'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Instansi'),
                Tables\Columns\TextColumn::make('slug')
                    ->badge()
                    ->label('Slug'),
                Tables\Columns\ColorColumn::make('primary_color')
                    ->label('Tema'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('generate_users')
                    ->label('Generate Default Users')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Hasilkan Akun Default?')
                    ->modalDescription('Sistem akan membuat 5 akun (Admin, Operator, Verifikator, Pimpinan, Bendahara) untuk instansi ini dengan password "bedas2026".')
                    ->action(function (Instansi $record) {
                        \Illuminate\Support\Facades\Artisan::call('app:generate-instansi-users', [
                            'instansi_id' => $record->id,
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Akun Berhasil Dibuat')
                            ->body("5 Akun untuk {$record->name} telah berhasil di-generate.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInstansis::route('/'),
        ];
    }
}
