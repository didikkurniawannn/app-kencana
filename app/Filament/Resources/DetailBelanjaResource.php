<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetailBelanjaResource\Pages;
use App\Models\DetailBelanja;
use App\Filament\Resources\DetailBelanjaResource\RelationManagers\RevisionsRelationManager;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\Rekening;
use App\Helpers\ActiveYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class DetailBelanjaResource extends Resource
{
    protected static ?string $model = DetailBelanja::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';

    public static function getNavigationParentItem(): ?string
    {
        return 'Master RKA';
    }
    protected static ?int $navigationSort = 4;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }
    protected static ?string $navigationLabel = 'Master Detail Kegiatan';
    protected static ?string $modelLabel = 'Detail Kegiatan';
    protected static ?string $pluralModelLabel = 'Detail Kegiatan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Rekening')
                ->schema([
                    Forms\Components\Select::make('program_id')
                        ->label('Program')
                        ->options(function () {
                            $activeYear = ActiveYear::get();
                            return Program::where('tahun_anggaran', $activeYear)->pluck('nama_program', 'id');
                        })
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('kegiatan_id', null)),

                    Forms\Components\Select::make('kegiatan_id')
                        ->label('Kegiatan')
                        ->options(function (callable $get) {
                            $programId = $get('program_id');
                            if (!$programId)
                                return [];
                            return Kegiatan::where('program_id', $programId)->pluck('nama_kegiatan', 'id');
                        })
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('sub_kegiatan_id', null)),

                    Forms\Components\Select::make('sub_kegiatan_id')
                        ->label('Sub Kegiatan')
                        ->options(function (callable $get) {
                            $kegiatanId = $get('kegiatan_id');
                            if (!$kegiatanId)
                                return [];
                            return SubKegiatan::where('kegiatan_id', $kegiatanId)->pluck('nama_sub_kegiatan', 'id');
                        })
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('rekening_id', null)),

                    Forms\Components\Select::make('rekening_id')
                        ->label('Rekening')
                        ->options(function (callable $get) {
                            $subKegiatanId = $get('sub_kegiatan_id');
                            if (!$subKegiatanId)
                                return [];
                            return Rekening::where('sub_kegiatan_id', $subKegiatanId)
                                ->get()
                                ->mapWithKeys(fn($r) => [$r->id => $r->kode_rekening . ' - ' . $r->nama_rekening]);
                        })
                        ->required()
                        ->searchable(),
                ])->columns(2),

            Forms\Components\Section::make('Detail Kegiatan')
                ->schema([
                    Forms\Components\Textarea::make('nama_detail_belanja')
                        ->required()
                        ->label('Nama Detail Kegiatan')
                        ->rows(2),
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('kuefisien')
                            ->numeric()
                            ->label('Kuefisien')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $harga = (float) $get('harga');
                                $set('pagu', (float) $state * $harga);
                            }),
                        Forms\Components\TextInput::make('satuan')
                            ->label('Satuan'),
                        Forms\Components\TextInput::make('harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Harga')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $kuefisien = (float) $get('kuefisien');
                                $set('pagu', (float) $state * $kuefisien);
                            }),
                        Forms\Components\TextInput::make('pagu')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->label('Pagu')
                            ->helperText('Otomatis dihitung dari Kuefisien x Harga'),
                    ]),
                ]),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $activeYear = ActiveYear::get();
        return parent::getEloquentQuery()
            ->withCount('revisions')
            ->whereHas('rekening.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $activeYear));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rekening.kode_rekening')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_detail_belanja')
                    ->label('Detail Kegiatan')
                    ->wrap()
                    ->searchable()
                    ->icon(fn($record) => $record->revisions_count > 0 ? 'heroicon-s-arrows-right-left' : null)
                    ->iconColor('warning')
                    ->tooltip(fn($record) => $record->revisions_count > 0 ? 'Pernah mengalami pergeseran anggaran' : null)
                    ->limit(50),
                Tables\Columns\TextColumn::make('pagu_murni')
                    ->label('Pagu Murni')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pagu')
                    ->label('Pagu Saat Ini')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('realisasi_total')
                    ->label('Realisasi')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sisa_pagu')
                    ->label('Sisa Pagu')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn($record) => $record->sisa_pagu < 0 ? 'danger' : ($record->sisa_pagu == 0 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('persentase')
                    ->label('% Realisasi')
                    ->getStateUsing(function ($record) {
                        if ($record->pagu == 0)
                            return '0%';
                        return number_format(($record->realisasi_total / $record->pagu) * 100, 1) . '%';
                    })
                    ->badge()
                    ->color(fn($state) => floatval($state) >= 100 ? 'success' : (floatval($state) >= 50 ? 'warning' : 'gray')),
            ])
            ->filters([
                SelectFilter::make('rekening')
                    ->relationship('rekening', 'kode_rekening')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('geser_anggaran')
                    ->label('Geser Anggaran')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Placeholder::make('info_kuefisien')
                                ->label('Kuefisien Saat Ini')
                                ->content(fn($record) => $record->kuefisien . ' ' . $record->satuan),
                            Forms\Components\Placeholder::make('info_pagu')
                                ->label('Pagu Saat Ini')
                                ->content(fn($record) => 'Rp ' . number_format($record->pagu, 0, ',', '.')),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('kuefisien_baru')
                                ->label('Kuefisien Baru')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set, $get, $record) {
                                    $harga = (float) $record->harga;
                                    $set('pagu_baru', (float) $state * $harga);
                                })
                                ->default(fn($record) => $record->kuefisien),
                            Forms\Components\TextInput::make('pagu_baru')
                                ->label('Pagu Baru')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->default(fn($record) => $record->pagu),
                        ]),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Alasan Pergeseran')
                            ->required()
                            ->placeholder('Contoh: Penyesuaian kebutuhan operasional triwulan II'),
                    ])
                    ->action(function ($record, array $data) {
                        $paguLama = $record->pagu;
                        $kuefisienLama = $record->kuefisien;
                        $paguBaru = (float) $data['pagu_baru'];
                        $kuefisienBaru = (float) $data['kuefisien_baru'];
                        
                        // Create budget revision log
                        $record->revisions()->create([
                            'kuefisien_lama' => $kuefisienLama,
                            'kuefisien_baru' => $kuefisienBaru,
                            'pagu_lama' => $paguLama,
                            'pagu_baru' => $paguBaru,
                            'perubahan' => $paguBaru - $paguLama,
                            'keterangan' => $data['keterangan'],
                            'user_id' => auth()->id(),
                        ]);

                        // Update main record
                        $record->update([
                            'kuefisien' => $kuefisienBaru,
                            'pagu' => $paguBaru
                        ]);
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('pagu', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RevisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetailBelanjas::route('/'),
            'create' => Pages\CreateDetailBelanja::route('/create'),
            'edit' => Pages\EditDetailBelanja::route('/{record}/edit'),
        ];
    }
}
