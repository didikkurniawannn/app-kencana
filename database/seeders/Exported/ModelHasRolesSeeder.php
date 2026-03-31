<?php

namespace Database\Seeders\Exported;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasRolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_roles')->insert([
            [
                'role_id' => 1,
                'model_type' => 'App\\Models\\User',
                'model_id' => 1,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 8,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 11,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 12,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 13,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 15,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 16,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 17,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 18,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 20,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 21,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 22,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 23,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 25,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 26,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 27,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 28,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 30,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 31,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 32,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 33,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 35,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 36,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 37,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 38,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 40,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 41,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 42,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 43,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 45,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 46,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 47,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 48,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 50,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 51,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 52,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 53,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 55,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 56,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 57,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 58,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 60,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 61,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 62,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 63,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 65,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 66,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 67,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 68,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 70,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 71,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 72,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 73,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 75,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 76,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 77,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 78,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 80,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 81,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 82,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 83,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 85,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 86,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 87,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 88,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 90,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 91,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 92,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 93,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 95,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 96,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 97,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 98,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 100,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 101,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 102,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 103,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 105,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 106,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 107,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 108,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 110,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 111,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 112,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 113,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 115,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 116,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 117,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 118,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 120,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 121,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 122,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 123,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 125,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 126,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 127,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 128,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 130,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 131,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 132,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 133,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 135,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 136,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 137,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 138,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 140,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 141,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 142,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 143,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 145,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 146,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 147,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 148,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 150,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 151,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 152,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 153,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 155,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 156,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 157,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 158,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 160,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 161,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 162,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 163,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\\Models\\User',
                'model_id' => 165,
            ],
            [
                'role_id' => 10,
                'model_type' => 'App\\Models\\User',
                'model_id' => 166,
            ],
            [
                'role_id' => 11,
                'model_type' => 'App\\Models\\User',
                'model_id' => 167,
            ],
            [
                'role_id' => 7,
                'model_type' => 'App\\Models\\User',
                'model_id' => 168,
            ],
        ]);
    }
}
