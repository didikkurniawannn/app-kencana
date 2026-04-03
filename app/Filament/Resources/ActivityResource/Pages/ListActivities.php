<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importLog')
                ->label('Impor Log & Preview')
                ->icon('heroicon-m-document-arrow-up')
                ->color('info')
                ->modalHeading('Impor File Log untuk Pratinjau')
                ->modalDescription('Unggah file Excel hasil backup untuk dilihat isinya. Data HANYA akan disimpan di memori selama 10 menit dan TIDAK akan masuk ke database.')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Pilih File Excel (.xlsx)')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $path = storage_path('app/' . $data['file']);
                        $dataArray = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\ActivityImport, $path)[0];
                        
                        // Save to cache for 10 mins
                        $cacheKey = 'log_preview_' . auth()->id();
                        \Illuminate\Support\Facades\Cache::put($cacheKey, $dataArray, now()->addMinutes(10));
                        
                        // Clean up temp file
                        if (file_exists($path)) {
                            unlink($path);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Impor Berhasil')
                            ->body('Data telah dimuat ke pratinjau. Klik tombol "Lihat Pratinjau" di samping.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal Impor')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => auth()->user()->hasRole('super_admin')),

            \Filament\Actions\Action::make('viewPreview')
                ->label('Lihat Pratinjau (Sesaat)')
                ->icon('heroicon-m-magnifying-glass')
                ->color('success')
                ->modalHeading('Hasil Impor Log (Memori 10 Menit)')
                ->modalWidth(\Filament\Support\Enums\MaxWidth::Full)
                ->modalSubmitAction(false)
                ->modalContent(function () {
                    $cacheKey = 'log_preview_' . auth()->id();
                    $data = \Illuminate\Support\Facades\Cache::get($cacheKey);
                    
                    if (!$data) {
                        return \Illuminate\Support\Facades\Blade::render('<div class="p-4 text-center text-gray-500">Pratinjau telah kadaluarsa atau tidak ada.</div>');
                    }

                    return view('filament.pages.activity-preview', ['logs' => $data]);
                })
                ->visible(fn () => \Illuminate\Support\Facades\Cache::has('log_preview_' . auth()->id())),

            \Filament\Actions\Action::make('manageBackups')
                ->label('Kelola Backup')
                ->icon('heroicon-m-archive-box')
                ->color('gray')
                ->modalHeading('Manajemen File Backup Log')
                ->modalWidth(\Filament\Support\Enums\MaxWidth::FourExtraLarge)
                ->form([
                    \Filament\Forms\Components\Repeater::make('backups')
                        ->label('Daftar File Backup')
                        ->schema([
                            \Filament\Forms\Components\Grid::make(3)
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('filename')
                                        ->label('Nama File')
                                        ->disabled(),
                                    \Filament\Forms\Components\TextInput::make('size')
                                        ->label('Ukuran')
                                        ->disabled(),
                                    \Filament\Forms\Components\TextInput::make('created_at')
                                        ->label('Dibuat Pada')
                                        ->disabled(),
                                ]),
                            \Filament\Forms\Components\Actions::make([
                                \Filament\Forms\Components\Actions\Action::make('download')
                                    ->label('Unduh')
                                    ->icon('heroicon-m-arrow-down-tray')
                                    ->action(fn ($state) => response()->download(storage_path('app/backups/activity-logs/' . $state['filename']))),
                                \Filament\Forms\Components\Actions\Action::make('preview')
                                    ->label('Preview (10 Menit)')
                                    ->icon('heroicon-m-eye')
                                    ->color('info')
                                    ->action(function ($state) {
                                        try {
                                            $path = storage_path('app/backups/activity-logs/' . $state['filename']);
                                            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\ActivityImport, $path)[0];
                                            
                                            $cacheKey = 'log_preview_' . auth()->id();
                                            \Illuminate\Support\Facades\Cache::put($cacheKey, $data, now()->addMinutes(10));
                                            
                                            \Filament\Notifications\Notification::make()
                                                ->title('Preview Siap')
                                                ->body('Data backup telah dimuat ke memori selama 10 menit. Gunakan tombol "Lihat Pratinjau" di pojok kanan atas.')
                                                ->success()
                                                ->send();
                                        } catch (\Exception $e) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Gagal Membaca File')
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                                \Filament\Forms\Components\Actions\Action::make('deleteFile')
                                    ->label('Hapus')
                                    ->icon('heroicon-m-trash')
                                    ->color('danger')
                                    ->requiresConfirmation()
                                    ->action(function ($state) {
                                        $path = storage_path('app/backups/activity-logs/' . $state['filename']);
                                        if (file_exists($path)) {
                                            unlink($path);
                                            \Filament\Notifications\Notification::make()
                                                ->title('File Dihapus')
                                                ->success()
                                                ->send();
                                        }
                                    }),
                            ])->alignEnd(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->default(function () {
                            $path = storage_path('app/backups/activity-logs');
                            if (!is_dir($path)) return [];
                            
                            $files = glob($path . '/*.xlsx');
                            if (!$files) return [];

                            return collect($files)->map(fn ($file) => [
                                'filename' => basename($file),
                                'size' => round(filesize($file) / 1024, 2) . ' KB',
                                'created_at' => date('d/m/Y H:i', filemtime($file)),
                            ])->sortByDesc('created_at')->values()->toArray();
                        }),
                ])
                ->modalSubmitAction(false)
                ->visible(fn () => auth()->user()->hasRole('super_admin')),

            \Filament\Actions\Action::make('clearLog')
                ->label('Bersihkan Log & Backup')
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembersihan Log')
                ->modalDescription('Semua data yang dihapus AKAN DI-BACKUP otomatis ke format Excel sebelum dihapus permanen dari database.')
                ->form([
                    \Filament\Forms\Components\Radio::make('type')
                        ->label('Pilih Metode Pembersihan')
                        ->options([
                            'all' => 'Hapus Semua Log',
                            'month' => 'Hapus Log Bulan Tertentu',
                        ])
                        ->default('all')
                        ->reactive(),
                    \Filament\Forms\Components\Select::make('month')
                        ->label('Pilih Bulan')
                        ->options([
                            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ])
                        ->visible(fn ($get) => $get('type') === 'month')
                        ->required(fn ($get) => $get('type') === 'month'),
                    \Filament\Forms\Components\Select::make('year')
                        ->label('Pilih Tahun')
                        ->options(array_combine(range(date('Y'), 2020), range(date('Y'), 2020)))
                        ->visible(fn ($get) => $get('type') === 'month')
                        ->required(fn ($get) => $get('type') === 'month'),
                ])
                ->action(function (array $data) {
                    try {
                        $query = \App\Models\Activity::query()->withoutGlobalScopes();
                        $title = "Semua Log";
                        
                        if ($data['type'] === 'month') {
                            $query->whereMonth('created_at', $data['month'])
                                  ->whereYear('created_at', $data['year']);
                            $title = "Log Bulan " . $data['month'] . " " . $data['year'];
                        }

                        $logs = $query->get();
                        
                        if ($logs->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada data')
                                ->body('Tidak ada log yang ditemukan untuk kriteria ini.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // 1. Create Backup
                        $filename = 'backup_log_' . date('Ymd_His') . '.xlsx';
                        $path = 'backups/activity-logs/' . $filename;
                        
                        // Specify 'local' disk explicitly
                        \Maatwebsite\Excel\Facades\Excel::store(
                            new \App\Exports\ActivityExport($logs, $title),
                            $path,
                            'local'
                        );

                        // 2. Delete Data
                        $count = $logs->count();
                        $query->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil')
                            ->body("Berhasil mencadangkan {$count} baris ke file [{$filename}] dan menghapus log.")
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal Operasi')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => auth()->user()->hasRole('super_admin')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Aktivitas')
                ->icon('heroicon-o-queue-list')
                ->badge(fn () => $this->getBaseQuery()->count())
                ->badgeColor('primary'),

            'tambah' => Tab::make('Penambahan Data')
                ->icon('heroicon-o-plus-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event', 'created'))
                ->badge(fn () => $this->getBaseQuery()->where('event', 'created')->count())
                ->badgeColor('success'),

            'ubah' => Tab::make('Perubahan Data')
                ->icon('heroicon-o-pencil-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event', 'updated'))
                ->badge(fn () => $this->getBaseQuery()->where('event', 'updated')->count())
                ->badgeColor('info'),

            'hapus' => Tab::make('Penghapusan Data')
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event', 'deleted'))
                ->badge(fn () => $this->getBaseQuery()->where('event', 'deleted')->count())
                ->badgeColor('danger'),

            'auth' => Tab::make('Masuk & Keluar Sistem')
                ->icon('heroicon-o-finger-print')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('log_name', 'auth'))
                ->badge(fn () => $this->getBaseQuery()->where('log_name', 'auth')->count())
                ->badgeColor('warning'),
        ];
    }

    protected function getBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        $query = $this->getModel()::query()->withoutGlobalScopes();

        // Super Admin can see EVERYTHING for stats/tabs
        if ($user && $user->hasRole('super_admin')) {
            return $query;
        }

        $tenantId = \Filament\Facades\Filament::getTenant()?->id;
        $userId = $user?->id;

        if ($tenantId) {
            $query->where(function ($q) use ($tenantId, $userId) {
                $q->where('instansi_id', $tenantId)
                  ->orWhere(function ($sub) use ($userId) {
                      $sub->where('log_name', 'auth')
                          ->where('causer_id', $userId);
                  });
            });
        } else {
            $query->where('causer_id', $userId);
        }

        return $query;
    }
}
