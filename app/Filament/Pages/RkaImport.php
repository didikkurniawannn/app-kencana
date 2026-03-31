<?php

namespace App\Filament\Pages;

use App\Imports\RkaImporter;
use App\Exports\RkaExport;
use App\Models\Setting;
use App\Helpers\ActiveYear;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RkaImport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Import RKA';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.rka-import';

    public static function canAccess(): bool
    {
        return auth()->user()->can('page_RkaImport');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tahun_anggaran' => ActiveYear::get(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Impor Struktur Anggaran (RKA)')
                    ->description('Gunakan formulir ini untuk mengunggah struktur anggaran dari file Excel.')
                    ->schema([
                        TextInput::make('tahun_anggaran')
                            ->label('Tahun Anggaran Target')
                            ->numeric()
                            ->required(),
                        FileUpload::make('file')
                            ->label('File Excel RKA')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->disk('public')
                            ->directory('imports'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();
        $filePath = storage_path('app/public/' . $data['file']);
        $tenantId = Filament::getTenant()->id;

        try {
            Excel::import(new RkaImporter((int)$data['tahun_anggaran'], $tenantId), $filePath);

            Notification::make()
                ->title('Impor Berhasil')
                ->body('Struktur anggaran telah berhasil diperbarui.')
                ->success()
                ->send();

            $this->redirect(self::getUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Impor Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function export(): mixed
    {
        $tahun = (int) ($this->data['tahun_anggaran'] ?? ActiveYear::get());
        $tenantId = Filament::getTenant()->id;

        return (new RkaExport($tahun, $tenantId))->download("rka_{$tahun}.xlsx");
    }

    public function downloadTemplate(): mixed
    {
        $headers = [
            'kode_program',
            'nama_program',
            'kode_kegiatan',
            'nama_kegiatan',
            'kode_sub_kegiatan',
            'nama_sub_kegiatan',
            'kode_rekening',
            'nama_rekening',
            'nama_detail_belanja',
            'jumlah_kuefisien',
            'satuan',
            'harga_satuan'
        ];

        $sampleData = [
            [
                '1.01',
                'Program Pendidikan',
                '1.01.01',
                'Kegiatan Peningkatan Mutu',
                '1.01.01.001',
                'Sub Kegiatan Guru',
                '5.1.02.01',
                'Belanja Alat Tulis Kantor',
                'Kertas A4',
                '100',
                'Rim',
                '15000'
            ]
        ];

        return Excel::download(new class($headers, $sampleData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $headers;
            protected $data;
            public function __construct($headers, $data) { $this->headers = $headers; $this->data = $data; }
            public function headings(): array { return $this->headers; }
            public function array(): array { return $this->data; }
        }, 'template_rka.xlsx');
    }
}
