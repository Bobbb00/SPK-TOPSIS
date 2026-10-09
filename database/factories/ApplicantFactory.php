<?php

namespace Database\Factories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

/*
 * @extends Factory<Applicant>
 */
class ApplicantFactory extends Factory
{
    /*
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $nimCounter = 1;

        return [
            'nim'           => str_pad((string) $nimCounter++, 7, '2024', STR_PAD_LEFT),
            'name'          => fake('id_ID')->name(),
            'study_program' => fake()->randomElement([
                'Teknik Informatika',
                'Sistem Informasi',
                'Teknik Elektro',
                'Manajemen Informatika',
                'Ilmu Komputer',
            ]),
        ];
    }
}
