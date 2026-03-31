<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\ExpenseType;
use App\Models\ExpenseField;
use App\Models\Instansi;

return new class extends Migration
{
    public function up(): void
    {
        $instansis = Instansi::all();
        if ($instansis->isEmpty()) {
            $this->seedData(null);
        } else {
            foreach ($instansis as $instansi) {
                $this->seedData($instansi->id);
            }
        }
    }

    private function seedData($instansiId)
    {
        $expenseType = ExpenseType::create([
            'name' => 'Barang dan Jasa',
            'slug' => 'barang-jasa' . ($instansiId ? '-' . $instansiId : ''),
            'description' => 'Jenis pengeluaran untuk pembelian barang dan jasa',
            'is_active' => true,
            'instansi_id' => $instansiId,
        ]);

        $fields = [
            [
                'field_name' => 'nama_perusahaan',
                'field_label' => 'Nama Perusahaan/CV/Toko',
                'field_type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'field_name' => 'alamat_perusahaan',
                'field_label' => 'Alamat Perusahaan/Toko',
                'field_type' => 'textarea',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'field_name' => 'npwp',
                'field_label' => 'NPWP Perusahaan/Toko',
                'field_type' => 'text',
                'is_required' => false,
                'order' => 3,
            ],
            [
                'field_name' => 'nama_bank',
                'field_label' => 'Nama Bank Tujuan',
                'field_type' => 'text',
                'is_required' => true,
                'order' => 4,
            ],
            [
                'field_name' => 'rekening_bank',
                'field_label' => 'Nomor Rekening Bank',
                'field_type' => 'text',
                'is_required' => true,
                'order' => 5,
            ],
            [
                'field_name' => 'pemilik_rekening',
                'field_label' => 'Nama Pemilik Rekening',
                'field_type' => 'text',
                'is_required' => true,
                'order' => 6,
            ],
            [
                'field_name' => 'jenis_barang_jasa',
                'field_label' => 'Jenis Barang atau Jasa',
                'field_type' => 'select',
                'options' => ['Barang' => 'Barang', 'Jasa' => 'Jasa'],
                'is_required' => true,
                'order' => 7,
            ],
            [
                'field_name' => 'nomor_faktur',
                'field_label' => 'Nomor Faktur / Kwitansi',
                'field_type' => 'text',
                'is_required' => true,
                'order' => 8,
            ],
            [
                'field_name' => 'tanggal_faktur',
                'field_label' => 'Tanggal Faktur / Kwitansi',
                'field_type' => 'date',
                'is_required' => true,
                'order' => 9,
            ]
        ];

        foreach ($fields as $field) {
            $expenseType->fields()->create($field);
        }
    }

    public function down(): void
    {
        $types = ExpenseType::where('name', 'Barang dan Jasa')->get();
        foreach ($types as $type) {
            $type->fields()->delete();
            $type->delete();
        }
    }
};
