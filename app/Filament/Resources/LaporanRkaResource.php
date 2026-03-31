<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanRkaResource\Pages;
use App\Models\DetailBelanja;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Helpers\ActiveYear;
use App\Exports\RkaExport;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class LaporanRkaResource extends Resource
{
    protected static ?string $model = DetailBelanja::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan RKA/DPA';
    protected static ?string $modelLabel = 'Laporan RKA/DPA';
    protected static ?int $navigationSort = 4;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function canCreate(): bool { return false; }

    public static function getEloquentQuery(): Builder
    {
        $activeYear = ActiveYear::get();
        return parent::getEloquentQuery()
            ->whereHas('rekening.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $activeYear));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rekening.subKegiatan.kegiatan.program.kode_program')
                    ->label('Kode')
                    ->description(fn($record) => $record->rekening?->subKegiatan?->kegiatan?->program?->nama_program)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('full_path_names')
                    ->label('Program / Kegiatan / Sub / Rekening')
                    ->getStateUsing(function($record) {
                        return $record->rekening?->subKegiatan?->kegiatan?->nama_kegiatan;
                    })
                    ->description(fn($record) => $record->rekening?->nama_rekening)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_detail_belanja')
                    ->label('Detail Belanja (Uraian)')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagu_murni')
                    ->label('Anggaran Murni')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')->label('Total Murni')),
                Tables\Columns\TextColumn::make('pagu')
                    ->label('Anggaran Perubahan')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')->label('Total Perubahan')),
                Tables\Columns\TextColumn::make('selisih')
                    ->label('Selisih (+/-)')
                    ->getStateUsing(fn($record) => (float)$record->pagu - (float)($record->pagu_murni ?? $record->pagu))
                    ->money('IDR')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('program')
                    ->relationship('rekening.subKegiatan.kegiatan.program', 'nama_program')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('kegiatan')
                    ->relationship('rekening.subKegiatan.kegiatan', 'nama_kegiatan')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_excel')
                    ->label('Export RKA (Excel)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Table $table) {
                        $query = $table->getLivewire()->getFilteredTableQuery();
                        return Excel::download(new RkaExport($query), 'laporan-rka-dpa-' . now()->format('Y-m-d') . '.xlsx');
                    })
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLaporanRkas::route('/'),
        ];
    }
}
