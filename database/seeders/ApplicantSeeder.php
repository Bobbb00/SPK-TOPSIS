<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    public function run(): void
    {
        $criterias = Criteria::all()->keyBy('code');

        if ($criterias->isEmpty()) {
            return;
        }

        $applicantsData = [
            [
                'nim' => '2024001',
                'name' => 'Ahmad Fauzi',
                'study_program' => 'Teknik Informatika',
                'scores' => ['C1' => 3.90, 'C2' => 2000000, 'C3' => 4, 'C4' => 85, 'C5' => 80],
            ],
            [
                'nim' => '2024002',
                'name' => 'Budi Santoso',
                'study_program' => 'Sistem Informasi',
                'scores' => ['C1' => 3.75, 'C2' => 3500000, 'C3' => 3, 'C4' => 70, 'C5' => 85],
            ],
            [
                'nim' => '2024003',
                'name' => 'Citra Dewi',
                'study_program' => 'Teknik Informatika',
                'scores' => ['C1' => 3.82, 'C2' => 1800000, 'C3' => 5, 'C4' => 90, 'C5' => 75],
            ],
            [
                'nim' => '2024004',
                'name' => 'Dedi Pratama',
                'study_program' => 'Teknik Komputer',
                'scores' => ['C1' => 3.45, 'C2' => 5000000, 'C3' => 2, 'C4' => 60, 'C5' => 65],
            ],
            [
                'nim' => '2024005',
                'name' => 'Eka Rahmawati',
                'study_program' => 'Sistem Informasi',
                'scores' => ['C1' => 3.65, 'C2' => 2200000, 'C3' => 4, 'C4' => 80, 'C5' => 90],
            ],
            [
                'nim' => '2024006',
                'name' => 'Fajar Nugraha',
                'study_program' => 'Teknik Informatika',
                'scores' => ['C1' => 3.95, 'C2' => 1500000, 'C3' => 3, 'C4' => 95, 'C5' => 85],
            ],
            [
                'nim' => '2024007',
                'name' => 'Gita Permata',
                'study_program' => 'Manajemen Informatika',
                'scores' => ['C1' => 3.30, 'C2' => 7000000, 'C3' => 1, 'C4' => 50, 'C5' => 60],
            ],
            [
                'nim' => '2024008',
                'name' => 'Hendra Setiawan',
                'study_program' => 'Teknik Komputer',
                'scores' => ['C1' => 3.70, 'C2' => 3000000, 'C3' => 3, 'C4' => 75, 'C5' => 70],
            ],
        ];

        foreach ($applicantsData as $data) {
            $applicant = Applicant::firstOrCreate(
                ['nim' => $data['nim']],
                [
                    'name' => $data['name'],
                    'study_program' => $data['study_program'],
                ]
            );

            foreach ($data['scores'] as $code => $score) {
                if (isset($criterias[$code])) {
                    Evaluation::updateOrCreate(
                        [
                            'applicant_id' => $applicant->id,
                            'criteria_id' => $criterias[$code]->id,
                        ],
                        [
                            'score' => $score,
                        ]
                    );
                }
            }
        }
    }
}
