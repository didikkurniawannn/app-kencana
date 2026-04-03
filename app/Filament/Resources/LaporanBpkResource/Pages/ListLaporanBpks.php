<?php

namespace App\Filament\Resources\LaporanBpkResource\Pages;

use App\Filament\Resources\LaporanBpkResource;
use App\Models\ExpenseType;
use App\Models\Realisasi;
use App\Imports\RealisasiBpkImport;
use App\Exports\RealisasiBpkTemplateExport;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\ActiveYear;

class ListLaporanBpks extends ListRecords
{
    protected static string $resource = LaporanBpkResource::class;

    public function getTabs(): array
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant?->id;

        $tabs = [];

        // Tab "Semua"
        $tabs['semua'] = Tab::make('Semua')
            ->icon('heroicon-o-squares-2x2')
            ->badge(fn () => Realisasi::query()
                ->where('instansi_id', $tenantId)
                ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($q) {
                    $activeYear = ActiveYear::get();
                    $q->where('tahun_anggaran', $activeYear);
                })
                ->count()
            )
            ->badgeColor('primary');

        // Get unique expense type names for this tenant, EXCLUDING SPJ RUTIN and SPJ BMHP
        $expenseTypes = ExpenseType::where('instansi_id', $tenantId)
            ->where('is_active', true)
            ->whereNotIn('name', ['SPJ RUTIN', 'SPJ BMHP'])
            ->get()
            ->unique('name');

        $iconMap = [
            'Belanja Pegawai' => 'heroicon-o-users',
            'Barang dan Jasa' => 'heroicon-o-shopping-cart',
            'Pemeliharaan' => 'heroicon-o-wrench-screwdriver',
            'Makan dan Minum (Hotel)' => 'heroicon-o-building-office',
            'Makan dan Minum (Katering)' => 'heroicon-o-cake',
            'Perjalanan Dinas Dalam Daerah' => 'heroicon-o-map-pin',
            'Perjalanan Dinas Luar Daerah' => 'heroicon-o-paper-airplane',
        ];

        foreach ($expenseTypes as $expenseType) {
            $name = $expenseType->name;
            $slug = \Illuminate\Support\Str::slug($name);

            // Get all expense_type_ids with this name for this tenant
            $expenseTypeIds = ExpenseType::where('instansi_id', $tenantId)
                ->where('name', $name)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            $icon = $iconMap[$name] ?? 'heroicon-o-document-text';

            $tabs[$slug] = Tab::make($name)
                ->icon($icon)
                ->badge(fn () => Realisasi::query()
                    ->where('instansi_id', $tenantId)
                    ->whereIn('expense_type_id', $expenseTypeIds)
                    ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($q) {
                        $activeYear = ActiveYear::get();
                        $q->where('tahun_anggaran', $activeYear);
                    })
                    ->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('expense_type_id', $expenseTypeIds));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant?->id;

        // Get expense types for import dropdown (excluding SPJ RUTIN & SPJ BMHP)
        $expenseTypes = ExpenseType::where('instansi_id', $tenantId)
            ->where('is_active', true)
            ->whereNotIn('name', ['SPJ RUTIN', 'SPJ BMHP'])
            ->get()
            ->unique('name')
            ->mapWithKeys(fn ($et) => [$et->id => $et->name])
            ->toArray();

        return [
            Actions\Action::make('import_realisasi')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn () => auth()->user()->hasAnyRole(['operator', 'super_admin']))
                ->form([
                    \Filament\Forms\Components\Select::make('expense_type_id')
                        ->label('Jenis Pengadaan')
                        ->options($expenseTypes)
                        ->required()
                        ->helperText('Pilih jenis pengadaan untuk data yang akan diimport'),
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('File Excel')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required()
                        ->helperText('Format: .xlsx, .xls, atau .csv'),
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = storage_path('app/' . $data['file']);

                        $import = new RealisasiBpkImport($data['expense_type_id']);
                        Excel::import($import, $filePath);

                        $results = $import->getResults();

                        // Clean up temp file
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }

                        $message = "Berhasil: {$results['success']} data diimport.";
                        if ($results['skipped'] > 0) {
                            $message .= " Dilewati: {$results['skipped']}.";
                        }

                        if (!empty($results['errors'])) {
                            $errorList = implode("\n", array_slice($results['errors'], 0, 5));
                            if (count($results['errors']) > 5) {
                                $errorList .= "\n...dan " . (count($results['errors']) - 5) . " error lainnya";
                            }
                            Notification::make()
                                ->title('Import Selesai (dengan peringatan)')
                                ->body($message . "\n\nError:\n" . $errorList)
                                ->warning()
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import Berhasil!')
                                ->body($message)
                                ->success()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => auth()->user()->hasAnyRole(['operator', 'super_admin']))
                ->form([
                    \Filament\Forms\Components\Select::make('expense_type_id')
                        ->label('Jenis Pengadaan')
                        ->options($expenseTypes)
                        ->required()
                        ->helperText('Pilih jenis pengadaan untuk generate template'),
                ])
                ->action(function (array $data) {
                    $expenseType = ExpenseType::find($data['expense_type_id']);
                    $name = $expenseType ? \Illuminate\Support\Str::slug($expenseType->name) : 'template';
                    return Excel::download(
                        new RealisasiBpkTemplateExport($data['expense_type_id']),
                        "template-import-bpk-{$name}-" . now()->format('Y-m-d') . '.xlsx'
                    );
                }),
        ];
    }
}

