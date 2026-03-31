<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Log Activity';
    protected static ?string $modelLabel = 'Activity';
    protected static ?string $pluralModelLabel = 'Log Activity';
    protected static ?int $navigationSort = 10;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'operator']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name')->label('Kategori')->disabled(),
                Forms\Components\TextInput::make('description')->label('Aksi')->disabled(),
                Forms\Components\TextInput::make('subject_type')->label('Modul/Model')->disabled(),
                Forms\Components\TextInput::make('subject_id')->label('ID Data')->disabled(),
                Forms\Components\KeyValue::make('properties.attributes')
                    ->label('Nilai Saat Ini (Attributes)')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('properties.old')
                    ->label('Nilai Sebelumnya (Old)')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Kejadian')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pelaku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Tindakan')
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modul')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return '-';
                        $classParts = explode('\\', $state);
                        $modelName = end($classParts);
                        return $modelName . ' (ID: ' . $record->subject_id . ')';
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('waktu_cepat')
                    ->label('Filter Waktu Praktis')
                    ->options([
                        'today' => 'Hari Ini',
                        'this_week' => 'Minggu Ini',
                        'last_week' => 'Minggu Lalu',
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $now = Carbon::now();
                        match ($data['value']) {
                            'today' => $query->whereDate('created_at', $now->toDateString()),
                            'this_week' => $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
                            'last_week' => $query->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]),
                            'this_month' => $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year),
                            'last_month' => $query->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year),
                            default => $query,
                        };
                    }),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}
