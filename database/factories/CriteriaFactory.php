<?php

namespace Database\Factories;

use App\Models\Criteria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Criteria>
 */
class CriteriaFactory extends Factory
{
    /*
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $codeCounter = 1;

        return [
            'code'   => 'C' . $codeCounter++,
            'name'   => fake()->randomElement([
                'IPK Mahasiswa',
                'Penghasilan Orang Tua',
                'Jumlah Tanggungan',
                'Prestasi Non-Akademik',
                'Jarak Domisili',
            ]),
            'weight' => 0.25,
            'type'   => fake()->randomElement(['benefit', 'cost']),
        ];
    }

    /**
     * Set sebagai kriteria benefit (nilai besar lebih baik).
     */
    public function benefit(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'benefit']);
    }

    /**
     * Set sebagai kriteria cost (nilai kecil lebih baik).
     */
    public function cost(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'cost']);
    }
}
