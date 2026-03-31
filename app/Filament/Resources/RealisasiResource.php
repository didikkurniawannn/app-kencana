<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RealisasiResource\Pages;
use App\Models\Realisasi;
use App\Models\DetailBelanja;
use App\Models\SubKegiatan;
use App\Models\Rekening;
use App\Models\ExpenseType;
use App\Models\ExpenseField;
use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Closure;
use ZipArchive;
use App\Helpers\ActiveYear;

class RealisasiResource extends Resource
{
    protected static ?string $model = Realisasi::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;
    protected static ?string $tenantOwnershipRelationshipName = 'instansi';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = static::getEloquentQuery();
        
        if ($user->hasRole('verifikator')) {
            return (string) $query->where('status', 'diajukan')->count();
        }
        
        if ($user->hasRole('pimpinan')) {
            return (string) $query->where('status', 'verifikasi')->count();
        }
        
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    protected static ?string $navigationLabel = 'Data Realisasi Anggaran';
    protected static ?string $modelLabel = 'Data Realisasi Anggaran';
    protected static ?string $pluralModelLabel = 'Data Realisasi Anggaran';

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_instansi']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pilih Detail Belanja')
                ->schema([
                    Forms\Components\Select::make('sub_kegiatan_id')
                        ->label('Sub Kegiatan')
                        ->options(function () {
                            $activeYear = ActiveYear::get();
                            return SubKegiatan::query()
                                ->whereHas('kegiatan.program', fn($q) => $q->where('tahun_anggaran', $activeYear))
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    return [$item->id => $item->kode_sub_kegiatan . ' - ' . substr($item->nama_sub_kegiatan, 0, 60)];
                                });
                        })
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('rekening_id', null);
                            $set('detail_belanja_id', null);
                            $set('sisa_pagu_display', '-');
                        })
                        ->dehydrated(false),

                    Forms\Components\Select::make('rekening_id')
                        ->label('Kode Rekening')
                        ->options(function (callable $get) {
                            $subKegiatanId = $get('sub_kegiatan_id');
                            if (!$subKegiatanId)
                                return [];

                            return Rekening::where('sub_kegiatan_id', $subKegiatanId)
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    return [$item->id => $item->kode_rekening . ' - ' . $item->nama_rekening];
                                });
                        })
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('detail_belanja_id', null);
                            $set('sisa_pagu_display', '-');
                        })
                        ->dehydrated(false),

                    Forms\Components\Select::make('detail_belanja_id')
                        ->label('Detail Belanja')
                        ->options(function (callable $get) {
                            $rekeningId = $get('rekening_id');
                            if (!$rekeningId)
                                return [];

                            return DetailBelanja::where('rekening_id', $rekeningId)
                                ->where('sisa_pagu', '>', 0)
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    $sisaPagu = 'Rp ' . number_format($item->sisa_pagu, 0, ',', '.');
                                    $sisaKuef = number_format($item->sisa_kuefisien, 2, ',', '.') . ' ' . $item->satuan;
                                    return [$item->id => substr($item->nama_detail_belanja, 0, 70) . " (Sisa: $sisaPagu | $sisaKuef)"];
                                });
                        })
                        ->required(false)
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            if ($state) {
                                $detail = DetailBelanja::find($state);
                                if ($detail) {
                                    $set('harga_satuan', (float) $detail->harga);
                                    $set('pagu_awal', 'Rp ' . number_format((float) $detail->pagu, 0, ',', '.'));
                                    $set('sisa_pagu_display', 'Rp ' . number_format((float) $detail->sisa_pagu, 0, ',', '.'));
                                    $set('kuefisien_awal', number_format((float) $detail->kuefisien, 2, ',', '.') . ' ' . $detail->satuan);
                                    $set('sisa_kuefisien_display', number_format((float) $detail->sisa_kuefisien, 2, ',', '.') . ' ' . $detail->satuan);
                                }
                            } else {
                                $set('harga_satuan', 0);
                                $set('pagu_awal', null);
                                $set('sisa_pagu_display', null);
                                $set('kuefisien_awal', null);
                                $set('sisa_kuefisien_display', null);
                            }
                        }),

                    Forms\Components\Hidden::make('harga_satuan')
                        ->dehydrated(false),

                    Forms\Components\Group::make([
                        Forms\Components\Placeholder::make('kuefisien_awal')
                            ->label('Kuefisien Awal')
                            ->content(fn($get) => $get('kuefisien_awal') ?? '-'),
                        Forms\Components\Placeholder::make('sisa_kuefisien_display')
                            ->label('Sisa Kuefisien')
                            ->content(fn($get) => $get('sisa_kuefisien_display') ?? '-'),
                        Forms\Components\Placeholder::make('pagu_awal')
                            ->label('Pagu Awal')
                            ->content(fn($get) => $get('pagu_awal') ?? '-'),
                        Forms\Components\Placeholder::make('sisa_pagu_display')
                            ->label('Sisa Pagu')
                            ->content(fn($get) => $get('sisa_pagu_display') ?? '-'),
                    ])
                        ->columns(2)
                        ->visible(fn($get) => $get('detail_belanja_id') !== null),
                ])->columns(2),

            Forms\Components\Section::make('Jenis Pengeluaran')
                ->schema([
                    Forms\Components\Select::make('expense_type_id')
                        ->label('Jenis Pengeluaran')
                        ->options(function () {
                            $tenant = \Filament\Facades\Filament::getTenant();
                            return ExpenseType::where('is_active', true)
                                ->when($tenant, fn($q) => $q->where('instansi_id', $tenant->id))
                                ->pluck('name', 'id');
                        })
                        ->required(false)
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('dynamic_fields', [])),
                ]),

            Forms\Components\Section::make('Data Realisasi')
                ->schema([
                    Forms\Components\Select::make('sp2d_id')
                        ->label('Sumber Dana')
                        ->relationship(
                            'sp2d',
                            'nama_sumber_dana',
                            fn(Builder $query) =>
                            $query->whereYear('tanggal_sp2d', ActiveYear::get())
                                  ->where('is_active', true)
                                  ->where('status_verifikasi', 'diverifikasi')
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => ($record->nama_sumber_dana ?: ($record->sumber_dana . ' - ' . $record->nomor_sp2d)) . ' (Sisa: Rp ' . number_format((float)$record->sisa_jumlah, 0, ',', '.') . ')')
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih Sumber Dana')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $sp2d = \App\Models\Sp2d::find($state);
                                if ($sp2d) {
                                    $tanggal = \Carbon\Carbon::parse($sp2d->tanggal_sp2d);
                                    $info = "Sumber: {$sp2d->sumber_dana} | Tgl: {$tanggal->format('d/m/Y')} | Sisa: Rp " . number_format((float) $sp2d->sisa_jumlah, 0, ',', '.');
                                    $set('sp2d_info', $info);

                                    $set('dynamic_fields.nomor_sp2d', $sp2d->nomor_sp2d);
                                    $set('dynamic_fields.tanggal_sp2d', \Carbon\Carbon::parse($sp2d->tanggal_sp2d)->format('Y-m-d'));
                                    $set('dynamic_fields.nilai_sp2d', $sp2d->jumlah_sp2d);
                                }
                            } else {
                                $set('sp2d_info', null);
                                $set('dynamic_fields.nomor_sp2d', null);
                                $set('dynamic_fields.tanggal_sp2d', null);
                                $set('dynamic_fields.nilai_sp2d', null);
                            }
                        })
                        ->helperText(fn($get) => $get('sp2d_info')),

                    Forms\Components\DatePicker::make('tanggal_realisasi')
                        ->required(false)
                        ->default(now())
                        ->label('Tanggal Realisasi'),

                    Forms\Components\Select::make('pegawai_id')
                        ->label('Pegawai Terkait')
                        ->options(function () {
                            $tenant = \Filament\Facades\Filament::getTenant();
                            return Pegawai::query()
                                ->when($tenant, fn($q) => $q->where('instansi_id', $tenant->id))
                                ->get()
                                ->pluck('full_name', 'id');
                        })
                        ->searchable()
                        ->nullable()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $pegawai = Pegawai::find($state);
                                if ($pegawai) {
                                    $set('dynamic_fields.nip', $pegawai->nip);
                                    $set('dynamic_fields.pangkat', $pegawai->pangkat);
                                    $set('dynamic_fields.golongan', $pegawai->golongan);
                                    $set('dynamic_fields.jabatan', $pegawai->jabatan);
                                }
                            } else {
                                $set('dynamic_fields.nip', null);
                                $set('dynamic_fields.pangkat', null);
                                $set('dynamic_fields.golongan', null);
                                $set('dynamic_fields.jabatan', null);
                            }
                        }),

                    Forms\Components\TextInput::make('kuefisien')
                        ->label('Kuefisien Realisasi')
                        ->numeric()
                        ->required(false)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            $harga = $get('harga_satuan');
                            if ($harga > 0 && $state > 0) {
                                $set('jumlah', (float) $state * $harga);
                            }
                        }),

                    Forms\Components\TextInput::make('jumlah')
                        ->label('Jumlah Realisasi (Total)')
                        ->numeric()
                        ->integer()
                        ->required()
                        ->prefix('Rp')
                        ->live(onBlur: true)
                        ->formatStateUsing(fn ($state) => $state ? (int) $state : null)
                        ->helperText(fn (callable $get) => $get('jumlah') ? 'Rp ' . number_format((float) $get('jumlah'), 0, ',', '.') : null)
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            $harga = $get('harga_satuan');
                            if ($harga > 0 && $state > 0) {
                                $set('kuefisien', round((float) $state / $harga, 2));
                            }
                            $set('dynamic_fields.biaya_realisasi', (float) $state);
                        })
                        ->rules([
                            fn($get, $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                $detailId = $get('detail_belanja_id');
                                $sp2dId = $get('sp2d_id');

                                if ($detailId) {
                                    $detail = \App\Models\DetailBelanja::find($detailId);
                                    if ($detail) {
                                        $availableBudget = (float) $detail->sisa_pagu;
                                        if ($record && ($record->status === 'disetujui')) {
                                            $availableBudget += (float) $record->jumlah;
                                        }

                                        if ($value > $availableBudget) {
                                            $fail("Jumlah melebihi sisa pagu kegiatan tersedia (Rp " . number_format((float) $availableBudget, 0, ',', '.') . ")");
                                        }
                                    }
                                }

                                if ($sp2dId) {
                                    $sp2d = \App\Models\Sp2d::find($sp2dId);
                                    if ($sp2d) {
                                        $availableSp2d = (float) $sp2d->sisa_jumlah;
                                        if ($record && ($record->status === 'disetujui') && $record->sp2d_id == $sp2dId) {
                                            $availableSp2d += (float) $record->jumlah;
                                        }

                                        if ($value > $availableSp2d) {
                                            $fail("Jumlah melebihi sisa sumber dana tersedia (Rp " . number_format((float) $availableSp2d, 0, ',', '.') . ")");
                                        }
                                    }
                                }
                            }
                        ]),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->rows(2),

                    Forms\Components\FileUpload::make('bukti_file')
                        ->label('Upload Bukti (Nota/Kwitansi/Dokumen)')
                        ->disk('public')
                        ->directory(function (callable $get) {
                            $detailBelanjaId = $get('detail_belanja_id');
                            $tanggalRealisasi = $get('tanggal_realisasi');

                            if ($detailBelanjaId) {
                                $detail = \App\Models\DetailBelanja::find($detailBelanjaId);
                                if ($detail) {
                                    // Format date to Y-m-d if it exists
                                    $datePath = $tanggalRealisasi ? \Carbon\Carbon::parse($tanggalRealisasi)->format('Y-m-d') : null;
                                    return $detail->getDirectoryPath($datePath);
                                }
                            }
                            return 'bukti-realisasi/uncategorized';
                        })
                        ->multiple()
                        ->reorderable()
                        ->openable()
                        ->previewable()
                        ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(10240),

                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'dikembalikan' => 'Dikembalikan (Revisi)',
                            'diajukan' => 'Diajukan (Menunggu Verifikator)',
                            'verifikasi' => 'Terverifikasi (Menunggu Pimpinan)',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                        ])
                        ->default('draft')
                        ->required(),
                ])->columns(2),

            // Dynamic fields based on expense type
            Forms\Components\Section::make('Detail Pengeluaran')
                ->schema(function (callable $get) {
                    $expenseTypeId = $get('expense_type_id');
                    if (!$expenseTypeId) {
                        return [
                            Forms\Components\Placeholder::make('info')
                                ->content('Pilih jenis pengeluaran terlebih dahulu untuk melihat form detail.')
                        ];
                    }

                    $fields = ExpenseField::where('expense_type_id', $expenseTypeId)
                        ->orderBy('order')
                        ->get();

                    $formFields = [];
                    foreach ($fields as $field) {
                        $formField = match ($field->field_type) {
                            'text' => Forms\Components\TextInput::make("dynamic_fields.{$field->field_name}")
                                ->label($field->field_label)
                                ->required(false)
                                ->reactive(),
                            'number' => Forms\Components\TextInput::make("dynamic_fields.{$field->field_name}")
                                ->label($field->field_label)
                                ->numeric()
                                ->integer()
                                ->required(false)
                                ->live(onBlur: true)
                                ->formatStateUsing(fn ($state) => str_contains((string) $state, '.') ? (float) $state : (int) $state)
                                ->helperText(function (callable $get) use ($field) {
                                    $val = $get("dynamic_fields.{$field->field_name}");
                                    if (!$val) return null;
                                    if ($field->field_name === 'jumlah_pegawai') {
                                        return number_format((float) $val, 0, ',', '.') . ' Orang';
                                    }
                                    return 'Rp ' . number_format((float) $val, 0, ',', '.');
                                })
                                ->afterStateUpdated(function ($state, callable $set, callable $get) use ($field) {
                                        if ($field->field_name === 'biaya_realisasi' || $field->field_name === 'total_biaya_spd') {
                                            $set('jumlah', (float) $state);
                                            $harga = $get('harga_satuan');
                                            if ($harga > 0 && $state > 0) {
                                                $set('kuefisien', round((float) $state / $harga, 2));
                                            }
                                        }

                                        // Auto-sum for SPD costs
                                        $spdCostFields = [
                                        'uang_harian',
                                        'representasi',
                                        'biaya_bbm',
                                        'transport_lokal',
                                        'biaya_lain',
                                        'biaya_hotel',
                                        'biaya_tiket_pergi',
                                        'biaya_tiket_pulang',
                                        'biaya_transport_luar'
                                        ];
                                        if (in_array($field->field_name, $spdCostFields)) {
                                            $total = 0;
                                            foreach ($spdCostFields as $costField) {
                                                $total += (float) $get("dynamic_fields.{$costField}");
                                            }
                                            $set('dynamic_fields.total_biaya_spd', $total);
                                            $set('jumlah', $total);

                                            $harga = $get('harga_satuan');
                                            if ($harga > 0 && $total > 0) {
                                                $set('kuefisien', round((float) $total / $harga, 2));
                                            }
                                        }
                                    }),
                            'date' => Forms\Components\DatePicker::make("dynamic_fields.{$field->field_name}")
                                ->label($field->field_label)
                                ->required(false)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) use ($field) {
                                        if ($field->field_name === 'tgl_berangkat' || $field->field_name === 'tgl_kembali') {
                                            $berangkat = $get('dynamic_fields.tgl_berangkat');
                                            $kembali = $get('dynamic_fields.tgl_kembali');

                                            if ($berangkat && $kembali) {
                                                $date1 = \Carbon\Carbon::parse($berangkat);
                                                $date2 = \Carbon\Carbon::parse($kembali);
                                                $diff = $date1->diffInDays($date2) + 1;
                                                $set('dynamic_fields.jumlah_hari', "{$diff} Hari");
                                            }
                                        }
                                    }),
                            'textarea' => Forms\Components\Textarea::make("dynamic_fields.{$field->field_name}")
                                ->label($field->field_label)
                                ->rows(3)
                                ->required(false)
                                ->reactive(),
                            'select' => Forms\Components\Select::make("dynamic_fields.{$field->field_name}")
                                ->label($field->field_label)
                                ->options($field->options ?? [])
                                ->required(false),
                            'file' => Forms\Components\FileUpload::make("dynamic_fields.{$field->field_name}")
                                ->label($field->field_label)
                                ->disk('public')
                                ->directory(function (callable $get) {
                                        $detailBelanjaId = $get('detail_belanja_id');
                                        $tanggalRealisasi = $get('tanggal_realisasi');

                                        if ($detailBelanjaId) {
                                            $detail = \App\Models\DetailBelanja::find($detailBelanjaId);
                                            if ($detail) {
                                                // Format date to Y-m-d if it exists
                                                $datePath = $tanggalRealisasi ? \Carbon\Carbon::parse($tanggalRealisasi)->format('Y-m-d') : null;
                                                return $detail->getDirectoryPath($datePath);
                                            }
                                        }
                                        return 'bukti-realisasi/uncategorized';
                                    })
                                ->required(false),
                            default => Forms\Components\TextInput::make("dynamic_fields.{$field->field_name}")
                                ->label($field->field_label)
                                ->required(false),
                        };
                        $formFields[] = $formField;
                    }

                    return $formFields;
                })
                ->columns(2)
                ->visible(fn($get) => $get('expense_type_id') !== null),

            Forms\Components\Hidden::make('user_id')
                ->default(auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_realisasi')
                    ->label('Tanggal')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->isoFormat('D MMMM YYYY') : '-'),
                Tables\Columns\TextColumn::make('sp2d.nama_sumber_dana')
                    ->label('Sumber Dana')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->sp2d ? $record->sp2d->sumber_dana : null),
                Tables\Columns\TextColumn::make('detailBelanja.rekening.kode_rekening')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detailBelanja.nama_detail_belanja')
                    ->label('Detail Kegiatan')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),
                Tables\Columns\TextColumn::make('expenseType.name')
                    ->label('Jenis')
                    ->badge(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label('Pegawai')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'dikembalikan', 'ditolak' => 'danger',
                        'diajukan' => 'warning',
                        'verifikasi' => 'info',
                        'disetujui' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sp2d_id')
                    ->label('Sumber Dana')
                    ->options(fn () => \App\Models\Sp2d::where('instansi_id', \Filament\Facades\Filament::getTenant()->id)
                        ->where('is_active', true)
                        ->get()
                        ->mapWithKeys(function($sp2d) {
                            $sisa = number_format((float)$sp2d->sisa_jumlah, 0, ',', '.');
                            $nama = $sp2d->nama_sumber_dana ?: ($sp2d->sumber_dana . ' - ' . $sp2d->nomor_sp2d);
                            return [$sp2d->id => $nama . ' (Sisa: Rp ' . $sisa . ')'];
                        })
                        ->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('expense_type_id')
                    ->label('Jenis Pengeluaran')
                    ->options(fn () => \App\Models\ExpenseType::where('instansi_id', \Filament\Facades\Filament::getTenant()->id)->pluck('name', 'id')),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'dikembalikan' => 'Dikembalikan',
                        'diajukan' => 'Diajukan',
                        'verifikasi' => 'Verifikasi',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => in_array($record->status, ['draft', 'dikembalikan'])),
                Tables\Actions\Action::make('download_bukti')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn($record) => !empty($record->bukti_file) ? route('secure.download', ['path' => $record->bukti_file[0]]) : null)
                    ->visible(fn($record) => !empty($record->bukti_file)),
                Tables\Actions\Action::make('ajukan')
                    ->label('Ajukan (Operator)')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn($record) => in_array($record->status, ['draft', 'dikembalikan']) && auth()->user()->hasRole('operator'))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'diajukan']);
                        \App\Models\RealisasiLog::create(['realisasi_id' => $record->id, 'user_id' => auth()->id(), 'action' => 'diajukan']);
                        
                        $verifikators = \App\Models\User::byRoleAndTenant('verifikator', $record->instansi_id)->get();
                        Notification::make()
                            ->title('Pengajuan Realisasi Baru')
                            ->body("Operator " . auth()->user()->name . " telah mengajukan realisasi baru senilai Rp " . number_format($record->jumlah, 0, ',', '.') . " untuk diverifikasi.")
                            ->info()
                            ->icon('heroicon-o-paper-airplane')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->url(RealisasiResource::getUrl('index', ['tenant' => $record->instansi_id])),
                            ])
                            ->sendToDatabase($verifikators);

                        Notification::make()->title('SPJ Diajukan ke Verifikator')->success()->send();
                    }),
                Tables\Actions\Action::make('verif_spj')
                    ->label('Verifikasi SPJ')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'diajukan' && auth()->user()->hasRole('verifikator'))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'verifikasi']);
                        \App\Models\RealisasiLog::create(['realisasi_id' => $record->id, 'user_id' => auth()->id(), 'action' => 'verifikasi']);
                        
                        $pimpinans = \App\Models\User::byRoleAndTenant('pimpinan', $record->instansi_id)->get();
                        Notification::make()
                            ->title('Realisasi Menunggu Persetujuan')
                            ->body("Verifikator " . auth()->user()->name . " telah memverifikasi realisasi senilai Rp " . number_format($record->jumlah, 0, ',', '.') . ". Menunggu persetujuan akhir Anda.")
                            ->success()
                            ->icon('heroicon-o-check')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->url(RealisasiResource::getUrl('index', ['tenant' => $record->instansi_id])),
                            ])
                            ->sendToDatabase($pimpinans);

                        Notification::make()->title('SPJ Lanjut ke Pimpinan')->success()->send();
                    }),
                Tables\Actions\Action::make('setujui_akhir')
                    ->label('Setujui Akhir (Pimpinan)')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'verifikasi' && auth()->user()->hasRole('pimpinan'))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'disetujui']);
                        \App\Models\RealisasiLog::create(['realisasi_id' => $record->id, 'user_id' => auth()->id(), 'action' => 'disetujui']);
                        
                        $bendaharas = \App\Models\User::byRoleAndTenant('bendahara', $record->instansi_id)->get();
                        Notification::make()
                            ->title('Realisasi Disetujui - Siapkan Arsip')
                            ->body("Pimpinan telah menyetujui realisasi senilai Rp " . number_format($record->jumlah, 0, ',', '.') . ". Silakan lakukan pengarsipan dokumen SPJ.")
                            ->success()
                            ->icon('heroicon-o-check-badge')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->url(RealisasiResource::getUrl('index', ['tenant' => $record->instansi_id])),
                            ])
                            ->sendToDatabase($bendaharas);

                        // Also notify the original operator
                        $operator = $record->user;
                        if ($operator) {
                            Notification::make()
                                ->title('Realisasi Anda Disetujui')
                                ->body("Pengajuan realisasi Anda senilai Rp " . number_format($record->jumlah, 0, ',', '.') . " telah disetujui sepenuhnya oleh Pimpinan.")
                                ->success()
                                ->sendToDatabase($operator);
                        }

                        Notification::make()->title('SPJ Disetujui Sepenuhnya')->success()->send();
                    }),
                Tables\Actions\Action::make('tolak_pimpinan')
                    ->label('Tolak (Dibatalkan)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'verifikasi' && auth()->user()->hasRole('pimpinan'))
                    ->form([
                        Forms\Components\Textarea::make('catatan')->label('Alasan Penolakan')->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['status' => 'ditolak']);
                        \App\Models\RealisasiLog::create(['realisasi_id' => $record->id, 'user_id' => auth()->id(), 'action' => 'ditolak', 'catatan' => $data['catatan']]);
                        
                        $operator = $record->user;
                        if ($operator) {
                            Notification::make()
                                ->title('Realisasi Ditolak Pimpinan')
                                ->body("Pengajuan realisasi Anda senilai Rp " . number_format($record->jumlah, 0, ',', '.') . " ditolak dengan catatan: " . $data['catatan'])
                                ->danger()
                                ->icon('heroicon-o-x-circle')
                                ->sendToDatabase($operator);
                        }

                        Notification::make()->title('SPJ Ditolak')->success()->send();
                    }),
                Tables\Actions\Action::make('upload_arsip')
                    ->label('Upload Arsip Final')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'disetujui' && auth()->user()->hasRole('bendahara'))
                    ->form([
                        Forms\Components\FileUpload::make('arsip_file')
                            ->label('Dokumen Arsip SPJ')
                            ->disk('public')
                            ->directory('arsip-final')
                            ->multiple()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $files = array_merge($record->bukti_file ?? [], $data['arsip_file']);
                        $record->update(['bukti_file' => $files]);
                        \App\Models\RealisasiLog::create(['realisasi_id' => $record->id, 'user_id' => auth()->id(), 'action' => 'diarsipkan', 'catatan' => 'Bendahara telah melampirkan berkas final']);
                        Notification::make()->title('Arsip Berhasil Disimpan')->success()->send();
                    }),
                Tables\Actions\Action::make('kembalikan')
                    ->label('Kembalikan ke Operator')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'diajukan' && auth()->user()->hasRole('verifikator'))
                    ->form([
                        Forms\Components\Textarea::make('catatan')->label('Catatan Revisi untuk Operator')->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['status' => 'dikembalikan']);
                        \App\Models\RealisasiLog::create(['realisasi_id' => $record->id, 'user_id' => auth()->id(), 'action' => 'dikembalikan', 'catatan' => $data['catatan']]);
                        
                        $operator = $record->user;
                        if ($operator) {
                            Notification::make()
                                ->title('Realisasi Dikembalikan (Revisi)')
                                ->body("Verifikator mengembalikan pengajuan realisasi Anda senilai Rp " . number_format($record->jumlah, 0, ',', '.') . " untuk direvisi. Catatan: " . $data['catatan'])
                                ->warning()
                                ->icon('heroicon-o-arrow-uturn-left')
                                ->sendToDatabase($operator);
                        }

                        Notification::make()->title('SPJ Dikembalikan untuk Direvisi')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('download')
                        ->label('Download Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $zipName = 'realisasi-' . now()->format('Y-m-d-His') . '.zip';
                            $zipPath = storage_path('app/public/' . $zipName);
                            $zip = new ZipArchive;

                            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                                foreach ($records as $record) {
                                    $files = $record->bukti_file ?? [];

                                    // Also check for dynamic file fields
                                    if ($record->expense_type_id) {
                                        $record_details = $record->details;
                                        // Since we don't have a direct list of which fields are files here easily without DB query,
                                        // and the main bukti_file is an array, we'll focus on that first.
                                        // But wait, the user wants ALL files.
                
                                        // The RealisasiDetail model stores field_name and field_value.
                                        // Some field_values might be file paths.
                                        foreach ($record_details as $detail) {
                                            // Check if it's a file path (starts with internal directory)
                                            if (Str::startsWith($detail->field_value, ['bukti-realisasi/', 'realisasi-files/'])) {
                                                $files[] = $detail->field_value;
                                            }
                                        }
                                    }

                                    $user = auth()->user();
                                    $password = $user->email;
                                    if (!empty($user->nip)) {
                                        $password = $user->nip;
                                    }

                                    foreach (array_unique($files) as $file) {
                                        if (Storage::disk('public')->exists($file)) {
                                            $fullPath = Storage::disk('public')->path($file);
                                            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

                                            if ($ext === 'pdf') {
                                                try {
                                                    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
                                                    $pageCount = $pdf->setSourceFile($fullPath);
                                                    $ownerPass = bin2hex(random_bytes(8));
                                                    $pdf->SetProtection(['print', 'copy'], $password, $ownerPass, 0, null);
                                                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                                                        $templateId = $pdf->importPage($pageNo);
                                                        $size = $pdf->getTemplateSize($templateId);
                                                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                                                        $pdf->useTemplate($templateId);
                                                    }
                                                    $zip->addFromString($file, $pdf->Output('', 'S'));
                                                } catch (\Exception $e) {
                                                    $zip->addFile($fullPath, $file);
                                                }
                                            } else {
                                                $zip->addFile($fullPath, $file);
                                            }
                                        }
                                    }
                                }
                                $zip->close();
                            }

                            return response()->download($zipPath)->deleteFileAfterSend(true);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Informasi Realisasi')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('tanggal_realisasi')->date('d/m/Y')->label('Tanggal'),
                        \Filament\Infolists\Components\TextEntry::make('sp2d.nomor_sp2d')->label('Nomor SPM')->copyable(),
                        \Filament\Infolists\Components\TextEntry::make('sp2d.sumber_dana')->label('Sumber Dana')->badge(),
                        \Filament\Infolists\Components\TextEntry::make('detailBelanja.nama_detail_belanja')->label('Detail Belanja'),
                        \Filament\Infolists\Components\TextEntry::make('expenseType.name')->label('Jenis Pengeluaran')->badge(),
                        \Filament\Infolists\Components\TextEntry::make('jumlah')->money('IDR')->label('Jumlah'),
                        \Filament\Infolists\Components\TextEntry::make('kuefisien')->label('Kuefisien'),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray',
                                'dikembalikan', 'ditolak' => 'danger',
                                'diajukan' => 'warning',
                                'verif_program', 'verif_keuangan' => 'info',
                                'finalisasi' => 'primary',
                                'disetujui' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state))),
                    ])->columns(2),

                \Filament\Infolists\Components\Section::make('Detail Tambahan')
                    ->schema(function ($record) {
                        $entries = [];
                        foreach ($record->details as $detail) {
                            $entries[] = \Filament\Infolists\Components\TextEntry::make("detail_{$detail->id}")
                                ->label(str_replace('_', ' ', ucfirst($detail->field_name)))
                                ->getStateUsing(fn() => $detail->field_value);
                        }
                        return $entries;
                    })->columns(2),

                \Filament\Infolists\Components\Section::make('Bukti Lampiran')
                    ->schema([
                        \Filament\Infolists\Components\ImageEntry::make('bukti_file')
                            ->label('')
                            ->disk('public')
                            ->stacked()
                            ->limit(5),
                        \Filament\Infolists\Components\TextEntry::make('bukti_file_list')
                            ->label('Daftar File (Klik untuk Buka)')
                            ->html()
                            ->getStateUsing(function ($record) {
                                if (!$record->bukti_file)
                                    return '-';
                                return collect($record->bukti_file)->map(function ($file) {
                                    $url = Storage::disk('public')->url($file);
                                    $name = basename($file);
                                    return "<a href='{$url}' target='_blank' style='color: #10b981; text-decoration: underline;'>{$name}</a>";
                                })->join('<br>');
                            })
                            ->visible(fn($record) => count($record->bukti_file ?? []) > 0),
                    ]),

                \Filament\Infolists\Components\Section::make('Riwayat Persetujuan & Revisi')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('logs')
                            ->label('')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->dateTime('d/m/Y H:i'),
                                \Filament\Infolists\Components\TextEntry::make('user.name')
                                    ->label('Oleh'),
                                \Filament\Infolists\Components\TextEntry::make('action')
                                    ->badge()
                                    ->label('Aksi')
                                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state))),
                                \Filament\Infolists\Components\TextEntry::make('catatan')
                                    ->label('Catatan')
                                    ->visible(fn($state) => filled($state)),
                            ])
                            ->columns(4)
                    ])
                    ->visible(fn($record) => $record->logs()->count() > 0),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRealisasis::route('/'),
            'create' => Pages\CreateRealisasi::route('/create'),
            'edit' => Pages\EditRealisasi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $activeYear = ActiveYear::get();

        return parent::getEloquentQuery()
            ->whereHas('detailBelanja.rekening.subKegiatan.kegiatan.program', function ($query) use ($activeYear) {
                $query->where('tahun_anggaran', $activeYear);
            });
    }
}
