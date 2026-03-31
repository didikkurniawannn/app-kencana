<?php

namespace App\Imports;

use App\Models\Realisasi;
use App\Models\DetailBelanja;
use App\Models\Sp2d;
use App\Models\Rekening;
use App\Models\RealisasiDetail;
use App\Models\ExpenseField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Filament\Facades\Filament;

class RealisasiBpkImport implements ToCollection, WithHeadingRow
{
    protected ?int $expenseTypeId;
    protected int $instansiId;
    protected int $userId;
    protected array $results = [
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function __construct(?int $expenseTypeId = null)
    {
        $this->expenseTypeId = $expenseTypeId;
        $this->instansiId = Filament::getTenant()?->id ?? 0;
        $this->userId = auth()->id();
    }

    public function collection(Collection $rows)
    {
        // Get dynamic fields for this expense type
        $dynamicFields = [];
        if ($this->expenseTypeId) {
            $dynamicFields = ExpenseField::where('expense_type_id', $this->expenseTypeId)
                ->orderBy('order')
                ->get();
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because header is row 1, index starts at 0

            try {
                // Skip empty rows
                if (empty($row['kode_rekening']) && empty($row['jumlah'])) {
                    $this->results['skipped']++;
                    continue;
                }

                // Lookup DetailBelanja by kode_rekening + nama_detail_belanja
                $detailBelanja = null;
                if (!empty($row['kode_rekening'])) {
                    $query = DetailBelanja::whereHas('rekening', function ($q) use ($row) {
                        $q->where('kode_rekening', $row['kode_rekening']);
                    })->where('instansi_id', $this->instansiId);

                    if (!empty($row['nama_detail_belanja'])) {
                        $query->where('nama_detail_belanja', 'LIKE', '%' . trim($row['nama_detail_belanja']) . '%');
                    }

                    $detailBelanja = $query->first();
                }

                if (!$detailBelanja) {
                    $this->results['errors'][] = "Baris {$rowNumber}: Detail belanja tidak ditemukan untuk kode rekening '{$row['kode_rekening']}'";
                    $this->results['skipped']++;
                    continue;
                }

                // Lookup SP2D
                $sp2dId = null;
                if (!empty($row['nomor_sp2d'])) {
                    $sp2d = Sp2d::where('nomor_sp2d', $row['nomor_sp2d'])
                        ->where('instansi_id', $this->instansiId)
                        ->where('is_active', true)
                        ->first();

                    if ($sp2d) {
                        $sp2dId = $sp2d->id;
                    } else {
                        $this->results['errors'][] = "Baris {$rowNumber}: SP2D '{$row['nomor_sp2d']}' tidak ditemukan atau tidak aktif";
                    }
                }

                // Parse date
                $tanggal = null;
                if (!empty($row['tanggal_realisasi'])) {
                    try {
                        if (is_numeric($row['tanggal_realisasi'])) {
                            $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_realisasi']);
                        } else {
                            $tanggal = \Carbon\Carbon::parse($row['tanggal_realisasi']);
                        }
                    } catch (\Exception $e) {
                        $tanggal = now();
                    }
                } else {
                    $tanggal = now();
                }

                // Create Realisasi
                $realisasi = Realisasi::create([
                    'detail_belanja_id' => $detailBelanja->id,
                    'expense_type_id' => $this->expenseTypeId,
                    'user_id' => $this->userId,
                    'sp2d_id' => $sp2dId,
                    'tanggal_realisasi' => $tanggal,
                    'kuefisien' => (float) ($row['kuefisien'] ?? 0),
                    'jumlah' => (float) ($row['jumlah'] ?? 0),
                    'keterangan' => $row['keterangan'] ?? null,
                    'status' => 'draft',
                    'instansi_id' => $this->instansiId,
                ]);

                // Save dynamic fields
                foreach ($dynamicFields as $field) {
                    $fieldName = $field->field_name;
                    if (isset($row[$fieldName]) && $row[$fieldName] !== null && $row[$fieldName] !== '') {
                        RealisasiDetail::create([
                            'realisasi_id' => $realisasi->id,
                            'field_name' => $fieldName,
                            'field_value' => (string) $row[$fieldName],
                            'instansi_id' => $this->instansiId,
                        ]);
                    }
                }

                $this->results['success']++;
            } catch (\Exception $e) {
                $this->results['errors'][] = "Baris {$rowNumber}: " . $e->getMessage();
                $this->results['skipped']++;
            }
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
