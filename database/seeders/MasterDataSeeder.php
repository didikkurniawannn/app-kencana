<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\Rekening;
use App\Models\DetailBelanja;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DetailBelanja::truncate();
        Rekening::truncate();
        SubKegiatan::truncate();
        Kegiatan::truncate();
        Program::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->importPrograms();
        $this->importKegiatan();
        $this->importSubKegiatan();
        $this->importRekening();
        $this->importDetailBelanja();

        $totalPagu = DetailBelanja::sum('pagu');
        $this->command->info("Total Anggaran di DB: " . number_format($totalPagu, 0, ',', '.'));

        if (abs($totalPagu - 4521281726) < 1) {
            $this->command->info("Total anggaran PERFECTLY MATCHED!");
        } else {
            $this->command->warn("Total anggaran MISMATCH! Diff: " . number_format($totalPagu - 4521281726, 0, ',', '.'));
        }
    }

    private function getReader($path)
    {
        $reader = IOFactory::createReaderForFile($path);
        return $reader;
    }

    private function formatCode($value)
    {
        if (is_numeric($value) && $value > 30000) {
            if ((int) $value === 36898)
                return "07.01.01";
        }
        return trim((string) $value);
    }

    private function parseNumber($value)
    {
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        return (float) ($value ?? 0);
    }

    private function importPrograms()
    {
        $this->command->info("Importing Programs...");
        $path = base_path('master_data/DATA REALISASI RKA 2026 - MASTER_PROGRAM.xlsx');
        $spreadsheet = $this->getReader($path)->load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                $data[$cell->getColumn()] = $cell->getValue();
            }
            if (empty($data['A']))
                continue;
            Program::updateOrCreate(['kode_program' => $this->formatCode($data['A'])], ['nama_program' => $data['B']]);
        }
        $spreadsheet->disconnectWorksheets();
    }

    private function importKegiatan()
    {
        $this->command->info("Importing Kegiatan...");
        $path = base_path('master_data/DATA REALISASI RKA 2026 - MASTER_KEGIATAN.xlsx');
        $spreadsheet = $this->getReader($path)->load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                $data[$cell->getColumn()] = $cell->getValue();
            }
            if (empty($data['A']))
                continue;
            $program = Program::where('kode_program', $this->formatCode($data['C']))->first();
            if ($program) {
                Kegiatan::updateOrCreate(['kode_kegiatan' => trim($data['A'])], ['nama_kegiatan' => $data['B'], 'program_id' => $program->id]);
            }
        }
        $spreadsheet->disconnectWorksheets();
    }

    private function importSubKegiatan()
    {
        $this->command->info("Importing SubKegiatan...");
        $path = base_path('master_data/DATA REALISASI RKA 2026 - MASTER_SUBKEGIATAN.xlsx');
        $spreadsheet = $this->getReader($path)->load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                $data[$cell->getColumn()] = $cell->getValue();
            }
            if (empty($data['A']))
                continue;
            $kegiatan = Kegiatan::where('kode_kegiatan', trim($data['C']))->first();
            if ($kegiatan) {
                SubKegiatan::updateOrCreate(['kode_sub_kegiatan' => trim($data['A'])], ['nama_sub_kegiatan' => $data['B'], 'kegiatan_id' => $kegiatan->id]);
            }
        }
        $spreadsheet->disconnectWorksheets();
    }

    private function importRekening()
    {
        $this->command->info("Importing Rekening...");
        $path = base_path('master_data/DATA REALISASI RKA 2026 - MASTER_REKENING.xlsx');
        $spreadsheet = $this->getReader($path)->load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                $data[$cell->getColumn()] = $cell->getValue();
            }
            if (empty($data['A']))
                continue;
            $subKegiatan = SubKegiatan::where('kode_sub_kegiatan', trim($data['C']))->first();
            if ($subKegiatan) {
                Rekening::updateOrCreate(['kode_rekening' => trim($data['A']), 'sub_kegiatan_id' => $subKegiatan->id], ['nama_rekening' => $data['B']]);
            }
        }
        $spreadsheet->disconnectWorksheets();
    }

    private function importDetailBelanja()
    {
        $this->command->info("Importing DetailBelanja...");
        $path = base_path('master_data/DATA REALISASI RKA 2026 - MASTER_DETAIL_BELANJA.xlsx');
        $spreadsheet = $this->getReader($path)->load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $count = 0;
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getColumn() === 'J') {
                    $data[$cell->getColumn()] = $cell->getCalculatedValue();
                } else {
                    $data[$cell->getColumn()] = $cell->getValue();
                }
            }

            if (empty($data['B']) && empty($data['C']))
                continue;

            $subKegiatanKode = trim((string) $data['F']);
            $subKegiatan = SubKegiatan::where('kode_sub_kegiatan', $subKegiatanKode)->first();

            if (!$subKegiatan && !empty($subKegiatanKode)) {
                $kegiatan = Kegiatan::first();
                $subKegiatan = SubKegiatan::create([
                    'kode_sub_kegiatan' => $subKegiatanKode,
                    'nama_sub_kegiatan' => "Sub Kegiatan (Auto-created)",
                    'kegiatan_id' => $kegiatan->id
                ]);
            }

            if (!$subKegiatan)
                continue;

            $rekeningKode = trim((string) $data['B']);
            $rekening = Rekening::where('kode_rekening', $rekeningKode)
                ->where('sub_kegiatan_id', $subKegiatan->id)
                ->first();

            if (!$rekening && !empty($rekeningKode)) {
                $rekening = Rekening::create([
                    'kode_rekening' => $rekeningKode,
                    'nama_rekening' => "Rekening (Auto-created)",
                    'sub_kegiatan_id' => $subKegiatan->id
                ]);
            }

            if (!$rekening)
                continue;

            DetailBelanja::create([
                'rekening_id' => $rekening->id,
                'nama_detail_belanja' => $data['C'],
                'kuefisien' => $this->parseNumber($data['G']),
                'satuan' => $data['H'],
                'harga' => $this->parseNumber($data['I']),
                'pagu' => $this->parseNumber($data['J']),
                'realisasi_total' => 0,
                'sisa_pagu' => $this->parseNumber($data['J']),
            ]);
            $count++;
        }
        $this->command->info("Imported $count detail belanja.");
        $spreadsheet->disconnectWorksheets();
    }
}
