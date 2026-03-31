<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ExpenseType;
use App\Models\ExpenseField;
use App\Models\Instansi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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
            'name' => 'Belanja Pegawai',
            'slug' => 'belanja-pegawai' . ($instansiId ? '-' . $instansiId : ''),
            'description' => 'Jenis pengeluaran untuk Belanja Pegawai',
            'is_active' => true,
            'instansi_id' => $instansiId,
        ]);

        $fields = [
            [
                'field_name' => 'bulan_pembayaran_gaji',
                'field_label' => 'Bulan Pembayaran Gaji/TPP',
                'field_type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'field_name' => 'jumlah_bruto',
                'field_label' => 'Jumlah Bruto',
                'field_type' => 'number',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'field_name' => 'jumlah_potongan',
                'field_label' => 'Jumlah Potongan',
                'field_type' => 'number',
                'is_required' => true,
                'order' => 3,
            ],
            [
                'field_name' => 'biaya_realisasi',
                'field_label' => 'Jumlah Yang dibayar',
                'field_type' => 'number',
                'is_required' => true,
                'order' => 4,
            ],
            [
                'field_name' => 'jumlah_pegawai',
                'field_label' => 'Jumlah Pegawai',
                'field_type' => 'number',
                'is_required' => true,
                'order' => 5,
            ]
        ];

        foreach ($fields as $field) {
            $expenseType->fields()->create($field);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $types = ExpenseType::where('name', 'Belanja Pegawai')->get();
        foreach ($types as $type) {
            $type->fields()->delete();
            $type->delete();
        }
    }
};
