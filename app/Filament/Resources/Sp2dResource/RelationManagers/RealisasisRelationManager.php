<?php

namespace App\Filament\Resources\Sp2dResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class RealisasisRelationManager extends RelationManager
{
    protected static string $relationship = 'realisasis';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Data Realisasi dari ' . ($ownerRecord->nama_sumber_dana ?? $ownerRecord->sumber_dana ?? '-');
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_realisasi')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->isoFormat('D MMMM YYYY') : '-'),
                Tables\Columns\TextColumn::make('detailBelanja.rekening.subKegiatan.kegiatan.program.kode_program')
                    ->label('Pro')
                    ->description(fn($record) => $record->detailBelanja?->rekening?->subKegiatan?->kegiatan?->program?->nama_program)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('detailBelanja.nama_detail_belanja')
                    ->label('Detail Belanja / Kegiatan')
                    ->wrap()
                    ->description(fn($record) => "Rekening: " . $record->detailBelanja?->rekening?->kode_rekening),
                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR')
                    ->label('Jumlah Realisasi'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'disetujui' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('download_all_evidence')
                    ->label('Download Semua Arsip (.zip)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function ($livewire) {
                        $record = $livewire->getOwnerRecord();
                        $realisasis = $record->realisasis()->whereNotNull('bukti_file')->get();

                        if ($realisasis->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada file untuk diunduh')
                                ->warning()
                                ->send();
                            return;
                        }

                        $zipName = 'arsip-' . str_replace('/', '-', $record->nomor_sp2d) . '.zip';
                        $zipPath = storage_path('app/public/' . $zipName);
                        $zip = new ZipArchive;

                        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                            foreach ($realisasis as $realisasi) {
                                $files = (array) $realisasi->bukti_file;
                                foreach ($files as $file) {
                                    $filePath = Storage::disk('public')->path($file);
                                    if (file_exists($filePath)) {
                                        $zip->addFile($filePath, basename($file));
                                    }
                                }
                            }
                            $zip->close();
                        }

                        return response()->download($zipPath)->deleteFileAfterSend(true);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('open_folder')
                    ->label('Buka Folder')
                    ->icon('heroicon-o-folder-open')
                    ->color('warning')
                    ->url(function ($record) {
                        $path = $record->detailBelanja?->getDirectoryPath();
                        return $path ? \App\Filament\Pages\FileDirectory::getUrl(['path' => $path]) : null;
                    })
                    ->visible(fn($record) => $record->detailBelanja !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
