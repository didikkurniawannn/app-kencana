<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanArsipResource\Pages;
use App\Models\Realisasi;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Exports\ArsipExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\ActiveYear;

class LaporanArsipResource extends Resource
{
    protected static ?string $model = Realisasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Laporan Registrasi Arsip';
    protected static ?string $modelLabel = 'Laporan Arsip';
    protected static ?string $pluralModelLabel = 'Laporan Arsip';
    protected static ?string $slug = 'laporan-registrasi-arsip';
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function getEloquentQuery(): Builder
    {
        $activeYear = ActiveYear::get();

        return parent::getEloquentQuery()
            ->whereNotNull('nomor_register') // Only registered archives
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($query) use ($activeYear) {
                $query->where('tahun_anggaran', $activeYear);
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_register')
                    ->label('No. Register')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tanggal_realisasi')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('detailBelanja.rekening.subKegiatan.kegiatan.program.nama_program')
                    ->label('Program')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('detailBelanja.rekening.subKegiatan.kegiatan.nama_kegiatan')
                    ->label('Kegiatan')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('arsip_sampul')
                    ->label('Sampul / Berkas')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('arsip_ruang')
                    ->label('Ruang')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('arsip_box')
                    ->label('Box')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status_arsip')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'proses' => 'warning',
                        'lengkap' => 'info',
                        'diarsipkan' => 'success',
                        default => 'gray',
                    })
                    ->label('Status'),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->options(fn() => Program::all()->pluck('nama_program', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('detailBelanja.rekening.subKegiatan.kegiatan', function ($q) use ($data) {
                                $q->where('program_id', $data['value']);
                            });
                        }
                    }),
                SelectFilter::make('kegiatan_id')
                    ->label('Kegiatan')
                    ->options(fn() => Kegiatan::all()->pluck('nama_kegiatan', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('detailBelanja.rekening.subKegiatan', function ($q) use ($data) {
                                $q->where('kegiatan_id', $data['value']);
                            });
                        }
                    }),
                SelectFilter::make('sumber_dana')
                    ->label('Sumber Dana')
                    ->options([
                        'GU' => 'GU',
                        'LS' => 'LS',
                        'TU' => 'TU',
                        'UP' => 'UP',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('sp2d', function ($q) use ($data) {
                                $q->where('sumber_dana', $data['value']);
                            });
                        }
                    }),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Table $table) {
                        $query = $table->getLivewire()->getFilteredTableQuery();
                        return Excel::download(new ArsipExport($query), 'laporan-registrasi-arsip-' . now()->format('Y-m-d') . '.xlsx');
                    }),
                Action::make('cetak_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (Table $table) {
                        // For PDF, we will pass serialized IDs to the controller
                        $ids = $table->getLivewire()->getFilteredTableQuery()->pluck('id')->toArray();
                        return redirect()->route('archive.register.print', ['ids' => implode(',', $ids)]);
                    }),
            ])
            ->actions([
                Action::make('label')
                    ->label('Cetak Label')
                    ->icon('heroicon-o-tag')
                    ->url(fn($record) => route('archive.label.realisasi.print', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->defaultSort('nomor_register', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanArsips::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
