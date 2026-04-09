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
    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $modelLabel = 'Aktivitas';
    protected static ?string $pluralModelLabel = 'Log Aktivitas';
    protected static ?int $navigationSort = 10;

    /**
     * Override Eloquent query to include auth logs (instansi_id null)
     * alongside tenant-scoped data logs.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = static::getModel()::query()->withoutGlobalScopes();

        // Super Admin can see EVERYTHING
        if ($user && $user->hasRole('super_admin')) {
            return $query;
        }

        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $userId = $user?->id;

        if ($tenantId) {
            $query->where(function (Builder $q) use ($tenantId, $userId) {
                // Logs for this tenant
                $q->where('instansi_id', $tenantId)
                  // OR Auth logs specifically for this user (even if instansi_id is null)
                  ->orWhere(function (Builder $sub) use ($userId) {
                      $sub->where('log_name', 'auth')
                          ->where('causer_id', $userId);
                  });
            });
        } else {
            // No tenant context, but user is not super_admin?
            // Only show THEIR OWN logs.
            $query->where('causer_id', $userId);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
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
                Forms\Components\Section::make('Informasi Aktivitas')
                    ->schema([
                        Forms\Components\TextInput::make('log_name')->label('Kategori')->disabled(),
                        Forms\Components\TextInput::make('description')->label('Keterangan')->disabled(),
                        Forms\Components\TextInput::make('subject_type')->label('Modul/Model')->disabled(),
                        Forms\Components\TextInput::make('subject_id')->label('ID Data')->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Perangkat')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')->label('Alamat IP')->disabled(),
                        Forms\Components\TextInput::make('user_agent')->label('Info Perangkat')->disabled()->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Data Perubahan')
                    ->schema([
                        Forms\Components\KeyValue::make('properties.attributes')
                            ->label('Nilai Saat Ini')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('properties.old')
                            ->label('Nilai Sebelumnya')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->searchable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pelaku')
                    ->searchable()
                    ->sortable()
                    ->default('Tidak diketahui')
                    ->description(fn ($record) => $record->causer?->email ?? ''),
                Tables\Columns\TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created'        => 'Menambahkan Data',
                        'updated'        => 'Mengubah Data',
                        'deleted'        => 'Menghapus Data',
                        'login'          => 'Masuk Sistem',
                        'logout'         => 'Keluar Sistem',
                        'login_failed'   => 'Gagal Masuk',
                        'password_reset' => 'Atur Ulang Kata Sandi',
                        default          => ucfirst($state ?? '-'),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created'        => 'success',
                        'updated'        => 'info',
                        'deleted'        => 'danger',
                        'login'          => 'success',
                        'logout'         => 'warning',
                        'login_failed'   => 'danger',
                        'password_reset' => 'info',
                        default          => 'gray',
                    })
                    ->icon(fn (?string $state): string => match ($state) {
                        'created'        => 'heroicon-m-plus-circle',
                        'updated'        => 'heroicon-m-pencil-square',
                        'deleted'        => 'heroicon-m-trash',
                        'login'          => 'heroicon-m-arrow-right-on-rectangle',
                        'logout'         => 'heroicon-m-arrow-left-on-rectangle',
                        'login_failed'   => 'heroicon-m-x-circle',
                        'password_reset' => 'heroicon-m-key',
                        default          => 'heroicon-m-information-circle',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->formatStateUsing(function (?string $state, $record): string {
                        $modul = '';
                        if ($record->subject_type) {
                            $classParts = explode('\\', $record->subject_type);
                            $modelName = end($classParts);
                            $modul = match ($modelName) {
                                'Realisasi'     => 'Realisasi',
                                'Sp2d'          => 'Sumber Dana (SP2D)',
                                'DetailBelanja' => 'Detail Belanja',
                                'Program'       => 'Program',
                                'Kegiatan'      => 'Kegiatan',
                                'SubKegiatan'   => 'Sub Kegiatan',
                                'User'          => 'Pengguna',
                                'Instansi'      => 'Instansi',
                                'Pegawai'       => 'Pegawai',
                                default         => $modelName,
                            };
                        }

                        $pelaku = $record->causer?->name ?? 'Sistem';

                        return match ($record->event) {
                            'created'        => "{$pelaku} menambahkan data {$modul}" . ($record->subject_id ? " #{$record->subject_id}" : ''),
                            'updated'        => "{$pelaku} mengubah data {$modul}" . ($record->subject_id ? " #{$record->subject_id}" : ''),
                            'deleted'        => "{$pelaku} menghapus data {$modul}" . ($record->subject_id ? " #{$record->subject_id}" : ''),
                            'login'          => "{$pelaku} masuk ke dalam sistem",
                            'logout'         => "{$pelaku} keluar dari sistem",
                            'login_failed'   => "Percobaan masuk gagal" . (isset($record->properties['email']) ? " ({$record->properties['email']})" : ''),
                            'password_reset' => "{$pelaku} mengatur ulang kata sandi",
                            default          => ucfirst($state ?? '-'),
                        };
                    })
                    ->wrap()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->badge()
                    ->color('gray')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->copyable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Perangkat')
                    ->formatStateUsing(function ($state, $record) {
                        if (empty($state)) return '-';
                        $browser = $record->browser;
                        $os = $record->device_os;
                        $icon = match ($browser) {
                            'Chrome'  => '🌐',
                            'Firefox' => '🦊',
                            'Safari'  => '🧭',
                            'Edge'    => '🔵',
                            'Opera'   => '🔴',
                            default   => '💻',
                        };
                        return "{$icon} {$browser} / {$os}";
                    })
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->tooltip(fn ($record) => $record->user_agent ?? '-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label('Jenis Aksi')
                    ->multiple()
                    ->options([
                        'created'        => 'Menambahkan Data',
                        'updated'        => 'Mengubah Data',
                        'deleted'        => 'Menghapus Data',
                        'login'          => 'Masuk Sistem',
                        'logout'         => 'Keluar Sistem',
                        'login_failed'   => 'Gagal Masuk',
                        'password_reset' => 'Atur Ulang Kata Sandi',
                    ]),
                SelectFilter::make('causer_id')
                    ->label('Pelaku')
                    ->options(fn () => \App\Models\User::pluck('name', 'id')->toArray())
                    ->searchable(),
                SelectFilter::make('waktu_cepat')
                    ->label('Rentang Waktu')
                    ->options([
                        'today'      => 'Hari Ini',
                        'yesterday'  => 'Kemarin',
                        'this_week'  => 'Minggu Ini',
                        'last_week'  => 'Minggu Lalu',
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) return $query;
                        $now = Carbon::now();
                        match ($data['value']) {
                            'today'      => $query->whereDate('created_at', $now->toDateString()),
                            'yesterday'  => $query->whereDate('created_at', $now->copy()->subDay()->toDateString()),
                            'this_week'  => $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
                            'last_week'  => $query->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]),
                            'this_month' => $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year),
                            'last_month' => $query->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year),
                            default      => $query,
                        };
                    }),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['created_until'], fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}
