<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ManageSettings extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()->can('page_ManageSettings');
    }
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Sistem';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'app_name' => Setting::get('app_name', config('app.name')),
            'app_description' => Setting::get('app_description', 'Sistem Penatausahaan Keuangan'),
            'tahun_anggaran_aktif' => Setting::get('tahun_anggaran_aktif', date('Y')),
            'primary_color' => Setting::get('primary_color', '#14797b'),
            'app_logo' => Setting::get('app_logo'),
            'app_favicon' => Setting::get('app_favicon'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Aplikasi')
                    ->description('Atur nama dan deskripsi aplikasi Anda.')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('Nama Aplikasi')
                            ->required(),
                        Textarea::make('app_description')
                            ->label('Deskripsi Aplikasi')
                            ->rows(3),
                    ])->columns(2),

                Section::make('Konfigurasi Anggaran')
                    ->description('Tentukan tahun anggaran aktif yang akan ditampilkan di seluruh sistem.')
                    ->schema([
                        TextInput::make('tahun_anggaran_aktif')
                            ->label('Tahun Anggaran Aktif')
                            ->numeric()
                            ->default(date('Y'))
                            ->required(),
                    ]),

                Section::make('Tema & Visual')
                    ->description('Kustomisasi warna dan logo aplikasi.')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('Warna Utama (Theme)')
                            ->default('#14797b'),
                        FileUpload::make('app_logo')
                            ->label('Logo Aplikasi')
                            ->image()
                            ->directory('settings')
                            ->disk('public')
                            ->imageEditor(),
                        FileUpload::make('app_favicon')
                            ->label('Favicon')
                            ->image()
                            ->directory('settings')
                            ->disk('public')
                            ->imageEditor(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();

        // Clean cache or redirect to refresh UI
        $this->redirect(route('filament.admin.pages.manage-settings', ['tenant' => \Filament\Facades\Filament::getTenant()]));
    }
}
