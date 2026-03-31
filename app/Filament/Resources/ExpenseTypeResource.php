<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseTypeResource\Pages;
use App\Models\ExpenseType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class ExpenseTypeResource extends Resource
{
    protected static ?string $model = ExpenseType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';
    protected static ?string $navigationLabel = 'Master Jenis Pengeluaran';
    protected static ?string $modelLabel = 'Jenis Pengeluaran';
    protected static ?string $pluralModelLabel = 'Jenis Pengeluaran';

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Jenis Pengeluaran')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Field Dinamis')
                ->schema([
                    Forms\Components\Repeater::make('fields')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('field_name')
                                ->label('Nama Field (tanpa spasi)')
                                ->required()
                                ->regex('/^[a-z_]+$/')
                                ->helperText('Gunakan huruf kecil dan underscore'),
                            Forms\Components\TextInput::make('field_label')
                                ->label('Label Field')
                                ->required(),
                            Forms\Components\Select::make('field_type')
                                ->label('Tipe Field')
                                ->options([
                                    'text' => 'Text',
                                    'number' => 'Number',
                                    'date' => 'Date',
                                    'textarea' => 'Textarea',
                                    'select' => 'Select (Dropdown)',
                                    'file' => 'File Upload',
                                ])
                                ->required(),
                            Forms\Components\Toggle::make('is_required')
                                ->label('Wajib Diisi')
                                ->default(false),
                            Forms\Components\TextInput::make('order')
                                ->label('Urutan')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Field'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->label('Slug'),
                Tables\Columns\TextColumn::make('fields_count')
                    ->counts('fields')
                    ->label('Jumlah Field'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseTypes::route('/'),
            'create' => Pages\CreateExpenseType::route('/create'),
            'edit' => Pages\EditExpenseType::route('/{record}/edit'),
        ];
    }
}
