<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Criteria;
use Illuminate\Database\Seeder;

class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCriterias = [
            [
                'code' => 'C1',
                'name' => 'Indeks Prestasi Kumulatif (IPK)',
                'weight' => 0.30,
                'type' => 'benefit',
            ],
            [
                'code' => 'C2',
                'name' => 'Penghasilan Orang Tua',
                'weight' => 0.25,
                'type' => 'cost',
            ],
            [
                'code' => 'C3',
                'name' => 'Jumlah Tanggungan Keluarga',
                'weight' => 0.20,
                'type' => 'benefit',
            ],
            [
                'code' => 'C4',
                'name' => 'Sertifikat Prestasi & Kejuaraan',
                'weight' => 0.15,
                'type' => 'benefit',
            ],
            [
                'code' => 'C5',
                'name' => 'Keaktifan Organisasi Kemahasiswaan',
                'weight' => 0.10,
                'type' => 'benefit',
            ],
        ];

        foreach ($defaultCriterias as $item) {
            Criteria::firstOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
