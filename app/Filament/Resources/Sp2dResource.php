<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp2dResource\Pages;
use App\Filament\Resources\Sp2dResource\RelationManagers;
use App\Models\Sp2d;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class Sp2dResource extends Resource
{
    protected static ?string $model = Sp2d::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Sumber Dana Kas';
    protected static ?string $modelLabel = 'Sumber Dana Kas';
    protected static ?string $pluralModelLabel = 'Sumber Dana Kas';

    protected static ?int $navigationSort = 0;

    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = static::getEloquentQuery();
        
        if ($user->hasRole('verifikator')) {
            return (string) $query->where('status_verifikasi', 'draft')->count();
        }
        
        if ($user->hasRole('pimpinan')) {
            return (string) $query->where('status_verifikasi', 'diverifikasi')->count();
        }
        
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $activeYear = \App\Helpers\ActiveYear::get();
        return parent::getEloquentQuery()->whereYear('tanggal_sp2d', $activeYear);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sumber Dana')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_sp2d')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Nomor SPM'),
                        Forms\Components\DatePicker::make('tanggal_sp2d')
                            ->required()
                            ->label('Tanggal SPM'),
                        Forms\Components\TextInput::make('jumlah_sp2d')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->prefix('Rp')
                            ->label('Jumlah Sumber Dana')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, $set) => $set('sisa_jumlah', $state)),
                        Forms\Components\Select::make('sumber_dana')
                            ->required()
                            ->options([
                                'GU' => 'GU (Ganti Uang)',
                                'LS' => 'LS (Langsung)',
                                'TU' => 'TU (Tambahan Uang)',
                                'UP' => 'UP (Uang Persediaan)',
                            ])
                            ->label('Sumber Dana'),
                        Forms\Components\TextInput::make('nama_sumber_dana')
                            ->maxLength(255)
                            ->label('Nama Sumber Dana')
                            ->placeholder('Misal: DAK, DAU, DBH, dll.'),
                        Forms\Components\TextInput::make('sisa_jumlah')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->prefix('Rp')
                            ->label('Sisa Jumlah')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Matikan agar anggaran ini tidak muncul lagi di pilihan form realisasi.'),
                        Forms\Components\Textarea::make('keterangan')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Section::make('Pelacakan Arsip')
                            ->schema([
                                Forms\Components\TextInput::make('lokasi_arsip_fisik')
                                    ->label('Lokasi Arsip Fisik')
                                    ->placeholder('Gudang A, Kotak 12, dll'),
                                Forms\Components\Select::make('status_arsip')
                                    ->options([
                                        'proses' => 'Sedang Diproses',
                                        'lengkap' => 'Dokumen Lengkap',
                                        'diarsipkan' => 'Sudah Diarsipkan',
                                    ])
                                    ->default('proses')
                                    ->label('Status Pengarsipan'),
                                Forms\Components\TextInput::make('kode_klasifikasi')
                                    ->label('Kode Klasifikasi ANRI')
                                    ->default('KU.01')
                                    ->placeholder('Misal: KU.01'),
                                Forms\Components\TextInput::make('masa_retensi')
                                    ->label('Masa Retensi (Tahun)')
                                    ->numeric()
                                    ->default(10)
                                    ->suffix('Tahun'),
                                Forms\Components\Select::make('tingkat_perkembangan')
                                    ->label('Tingkat Perkembangan')
                                    ->options([
                                        'Asli' => 'Asli',
                                        'Tembusan' => 'Tembusan',
                                        'Fotokopi' => 'Fotokopi',
                                    ])
                                    ->default('Asli'),
                            ])->columns(2),

                        Forms\Components\Section::make('Dokumen Pendukung')
                            ->schema([
                                Forms\Components\FileUpload::make('bukti_file')
                                    ->label('Bukti File (SPM, SPP, Sumber Dana)')
                                    ->disk('public')
                                    ->directory(fn($get) => 'sp2d-documents/' . \App\Helpers\ActiveYear::get() . '/' . \Illuminate\Support\Str::slug($get('nomor_sp2d') ?? 'unsaved'))
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(10240)
                                    ->multiple()
                                    ->reorderable()
                                    ->downloadable()
                                    ->openable()
                                    ->previewable()
                                    ->helperText('Upload semua dokumen terkait (SPM, SPP, Sumber Dana) di sini.'),
                            ])->columns(1)
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sumber_dana')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, 'GU') => 'warning',
                        str_contains($state, 'LS') => 'success',
                        str_contains($state, 'TU') => 'danger',
                        str_contains($state, 'UP') => 'info',
                        default => 'gray',
                    })
                    ->label('Sumber'),
                Tables\Columns\TextColumn::make('nama_sumber_dana')
                    ->label('Nama Sumber Dana')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nomor_sp2d')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor SPM'),
                Tables\Columns\TextColumn::make('tanggal_sp2d')
                    ->label('Tanggal')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->isoFormat('D MMMM YYYY') : '-'),
                Tables\Columns\TextColumn::make('jumlah_sp2d')
                    ->money('IDR')
                    ->sortable()
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('sisa_jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->label('Sisa (Saldo Kas)'),
                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'warning',
                        'diverifikasi' => 'success',
                        default => 'gray',
                    })
                    ->label('Status Validasi'),
                Tables\Columns\TextColumn::make('penggunaan')
                    ->label('Penggunaan Dana')
                    ->getStateUsing(function ($record) {
                        if ($record->jumlah_sp2d <= 0)
                            return 0;
                        $terpakai = $record->jumlah_sp2d - $record->sisa_jumlah;
                        return ($terpakai / $record->jumlah_sp2d) * 100;
                    })
                    ->view('filament.tables.columns.progress-bar'),
                Tables\Columns\TextColumn::make('status_arsip')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'proses' => 'warning',
                        'lengkap' => 'info',
                        'diarsipkan' => 'success',
                        default => 'gray',
                    })
                    ->label('Status Arsip'),
                Tables\Columns\TextColumn::make('lokasi_arsip_fisik')
                    ->label('Lokasi Fisik')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('file_count')
                    ->label('Dokumen')
                    ->getStateUsing(fn($record) => count($record->bukti_file ?? []) . ' Files')
                    ->icon('heroicon-o-paper-clip')
                    ->color('primary')
                    ->visible(fn($record) => !empty($record->bukti_file)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sumber_dana')
                    ->options([
                        'GU' => 'GU',
                        'LS' => 'LS',
                        'TU' => 'TU',
                        'UP' => 'UP',
                    ]),
                Tables\Filters\SelectFilter::make('status_verifikasi')
                    ->options([
                        'draft' => 'Draft',
                        'diverifikasi' => 'Diverifikasi',
                    ])
                    ->label('Filter Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status_verifikasi === 'draft' && auth()->user()->hasRole('verifikator'))
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Sumber Dana')
                    ->modalDescription('Apakah Anda yakin dokumen sumber dana ini sudah valid dan siap digunakan untuk realisasi?')
                    ->action(function ($record) {
                        $record->update(['status_verifikasi' => 'diverifikasi']);
                        
                        // Notify Operator (and maybe super_admin)
                        $operators = \App\Models\User::byRoleAndTenant('operator', $record->instansi_id)->get();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Sumber Dana Terverifikasi')
                            ->body("Sumber Dana dengan nomor SPM: {$record->nomor_sp2d} senilai Rp " . number_format($record->jumlah_sp2d, 0, ',', '.') . " telah diverifikasi dan siap digunakan.")
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->sendToDatabase($operators);
                        
                        // Notify Pimpinan (Hasil Akhir Berjenjang)
                        $pimpinans = \App\Models\User::byRoleAndTenant('pimpinan', $record->instansi_id)->get();
                        \Filament\Notifications\Notification::make()
                            ->title('Laporan Sumber Dana Selesai Diverifikasi')
                            ->body("Sumber Dana SPM: {$record->nomor_sp2d} senilai Rp " . number_format($record->jumlah_sp2d, 0, ',', '.') . " telah divalidasi oleh Verifikator.")
                            ->info()
                            ->icon('heroicon-o-document-check')
                            ->sendToDatabase($pimpinans);

                        \Filament\Notifications\Notification::make()->title('Sumber Dana Terverifikasi & Dilaporkan ke Pimpinan')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\RealisasisRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSp2ds::route('/'),
            'create' => Pages\CreateSp2d::route('/create'),
            'edit' => Pages\EditSp2d::route('/{record}/edit'),
        ];
    }
}
