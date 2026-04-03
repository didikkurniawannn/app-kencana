<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanBpkResource\Pages;
use App\Models\Realisasi;
use App\Models\ExpenseType;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\Rekening;
use App\Exports\RealisasiExport;
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
use Carbon\Carbon;
use App\Helpers\ActiveYear;

class LaporanBpkResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected static ?string $model = Realisasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Laporan BPK';
    protected static ?string $modelLabel = 'Laporan BPK';
    protected static ?string $pluralModelLabel = 'Laporan BPK';
    protected static ?string $slug = 'laporan-bpk';
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function getEloquentQuery(): Builder
    {
        $activeYear = ActiveYear::get();

        return parent::getEloquentQuery()
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($query) use ($activeYear) {
                $query->where('tahun_anggaran', $activeYear);
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_realisasi')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('expenseType.name')
                    ->label('Jenis Pengeluaran')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, 'Pegawai') => 'primary',
                        str_contains($state, 'Barang') => 'success',
                        str_contains($state, 'Pemeliharaan') => 'warning',
                        str_contains($state, 'Makan') => 'info',
                        str_contains($state, 'Perjalanan') => 'danger',
                        str_contains($state, 'SPJ') => 'gray',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('detailBelanja.rekening.subKegiatan.kegiatan.program.kode_program')
                    ->label('Pro')
                    ->description(fn($record) => $record->detailBelanja?->rekening?->subKegiatan?->kegiatan?->program?->nama_program)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('detailBelanja.rekening.kode_rekening')
                    ->label('Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detailBelanja.nama_detail_belanja')
                    ->label('Detail Belanja')
                    ->wrap()
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->label('Jumlah')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')->label('Total')),
                Tables\Columns\TextColumn::make('sp2d.nomor_sp2d')
                    ->label('Sumber Dana')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label('Pegawai')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'diajukan' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                    }),
            ])
            ->filters([
                Filter::make('tanggal_realisasi')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ]),
                        Forms\Components\Select::make('triwulan')
                            ->options([
                                '1' => 'Triwulan I (Jan-Mar)',
                                '2' => 'Triwulan II (Apr-Jun)',
                                '3' => 'Triwulan III (Jul-Sep)',
                                '4' => 'Triwulan IV (Okt-Des)',
                            ]),
                        Forms\Components\Select::make('semester')
                            ->options([
                                '1' => 'Semester I (Jan-Jun)',
                                '2' => 'Semester II (Jul-Dec)',
                            ]),
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['bulan'], fn($q) => $q->whereMonth('tanggal_realisasi', $data['bulan']))
                            ->when($data['triwulan'], function ($q, $tw) {
                                $months = match ($tw) {
                                    '1' => [1, 2, 3],
                                    '2' => [4, 5, 6],
                                    '3' => [7, 8, 9],
                                    '4' => [10, 11, 12],
                                };
                                return $q->where(function ($sub) use ($months) {
                                    foreach ($months as $month) {
                                        $sub->orWhereMonth('tanggal_realisasi', $month);
                                    }
                                });
                            })
                            ->when($data['semester'], function ($q, $sm) {
                                $months = $sm == '1' ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];
                                return $q->where(function ($sub) use ($months) {
                                    foreach ($months as $month) {
                                        $sub->orWhereMonth('tanggal_realisasi', $month);
                                    }
                                });
                            })
                            ->when($data['from'], fn($q) => $q->whereDate('tanggal_realisasi', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('tanggal_realisasi', '<=', $data['until']));
                    }),

                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'diajukan' => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Table $table) {
                        $query = $table->getLivewire()->getFilteredTableQuery();

                        // Detect expense type IDs from the active tab filter
                        $expenseTypeIds = null;
                        $clonedQuery = clone $query;
                        $filteredIds = $clonedQuery->pluck('expense_type_id')->unique()->filter()->values()->toArray();

                        if (!empty($filteredIds)) {
                            $expenseTypeIds = $filteredIds;
                        }

                        $tabName = $table->getLivewire()->activeTab ?? 'semua';
                        $fileName = 'laporan-bpk-' . $tabName . '-' . now()->format('Y-m-d') . '.xlsx';

                        return Excel::download(new RealisasiExport($query, $expenseTypeIds), $fileName);
                    })
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('tanggal_realisasi', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanBpks::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
