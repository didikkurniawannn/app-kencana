<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Management User';
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi User')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Nama'),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->label('Email'),
                    Forms\Components\TextInput::make('phone_number')
                        ->tel()
                        ->maxLength(255)
                        ->label('Nomor Handphone'),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $context): bool => $context === 'create')
                        ->label('Password')
                        ->helperText('Kosongkan jika tidak ingin mengubah password'),
                    // Field Role & Instansi hanya bisa diubah oleh admin
                    Forms\Components\Select::make('roles')
                        ->label('Role')
                        ->multiple()
                        ->relationship('roles', 'name', function ($query) {
                            if (!auth()->user()?->hasRole('super_admin')) {
                                $query->where('name', '!=', 'super_admin');
                            }
                        })
                        ->preload()
                        ->disabled(fn() => !auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']))
                        ->required(),
                    Forms\Components\Select::make('instansi')
                        ->label('Akses Instansi')
                        ->multiple()
                        ->relationship('instansi', 'name')
                        ->preload()
                        ->disabled(fn() => !auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']))
                        ->required()
                        ->helperText('Pilih satu atau lebih instansi yang dapat diakses oleh user ini.'),
                ])->columns(2),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        // 1. Jika Super Admin, biarkan melihat semua (tanpa filter tenant)
        if ($user?->hasRole('super_admin')) {
            return $query;
        }

        // 2. Jika Admin Instansi, hanya bisa lihat user di instansi (tenant) yang sedang aktif
        if ($user?->hasRole('admin_instansi')) {
            $tenantId = filament()->getTenant()?->id;
            return $query->whereHas('instansi', fn($q) => $q->where('instansis.id', $tenantId));
        }

        // 3. User biasa hanya bisa lihat dirinya sendiri
        return $query->where('id', $user?->id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Role'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->label('Dibuat'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) || auth()->id() === $record->id;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
