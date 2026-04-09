<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiResource\Pages;
use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PegawaiResource extends Resource
{
    protected static ?string $model = Pegawai::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 9;
    protected static ?string $navigationLabel = 'Master Pegawai';
    protected static ?string $modelLabel = 'Pegawai';
    protected static ?string $pluralModelLabel = 'Master Pegawai';
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Pegawai')
                ->schema([
                    Forms\Components\TextInput::make('nama')
                        ->required()
                        ->maxLength(255)
                        ->label('Nama Lengkap'),
                    Forms\Components\TextInput::make('nip')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->label('NIP'),
                    Forms\Components\TextInput::make('pangkat')
                        ->maxLength(100)
                        ->label('Pangkat'),
                    Forms\Components\TextInput::make('golongan')
                        ->maxLength(20)
                        ->label('Golongan'),
                    Forms\Components\TextInput::make('jabatan')
                        ->maxLength(100)
                        ->label('Jabatan'),
                    Forms\Components\Select::make('peran')
                        ->label('Peran')
                        ->options([
                            'PA/KPA' => 'PA/KPA (Menyetujui anggaran)',
                            'PPTK' => 'PPTK (Menjalankan teknis kegiatan)',
                            'PPK' => 'PPK (Mengurus kontrak dan tagihan)',
                            'PPSPM' => 'PPSPM (Memverifikasi & memerintahkan pembayaran SPM)',
                            'Bendahara' => 'Bendahara (Mengelola administrasi uang)',
                        ])
                        ->nullable(),
                    Forms\Components\FileUpload::make('file_perjanjian_kinerja')
                        ->label('File Perjanjian Kinerja')
                        ->disk('public')
                        ->directory('pegawai/perjanjian-kinerja')
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(10240)
                        ->columnSpanFull()
                        ->downloadable()
                        ->openable()
                        ->previewable(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('nip')
                    ->searchable()
                    ->label('NIP'),
                Tables\Columns\TextColumn::make('pangkat')
                    ->label('Pangkat'),
                Tables\Columns\TextColumn::make('golongan')
                    ->label('Golongan'),
                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan'),
                Tables\Columns\TextColumn::make('peran')
                    ->label('Peran')
                    ->badge(),
                Tables\Columns\TextColumn::make('file_perjanjian_kinerja')
                    ->label('File PK')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat / Download' : 'Tidak Ada')
                    ->url(fn ($record) => $record->file_perjanjian_kinerja ? route('secure.download', ['path' => $record->file_perjanjian_kinerja]) : null)
                    ->openUrlInNewTab()
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-text' : 'heroicon-o-x-circle'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_pk')
                    ->label('Download PK')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn($record) => !empty($record->file_perjanjian_kinerja) ? route('secure.download', ['path' => $record->file_perjanjian_kinerja]) : null)
                    ->visible(fn($record) => !empty($record->file_perjanjian_kinerja)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import_pegawai')
                    ->label('Import Pegawai')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('attachment')
                            ->label('File Excel/CSV')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $file = public_path('storage/' . $data['attachment']);
                        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\PegawaiImport, $file);
                        \Filament\Notifications\Notification::make()
                            ->title('Pegawai Imported')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('export_pegawai')
                    ->label('Export Pegawai')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->action(function () {
                        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PegawaiExport, 'pegawai-' . date('Y-m-d') . '.xlsx');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPegawais::route('/'),
            'create' => Pages\CreatePegawai::route('/create'),
            'edit' => Pages\EditPegawai::route('/{record}/edit'),
        ];
    }
}
