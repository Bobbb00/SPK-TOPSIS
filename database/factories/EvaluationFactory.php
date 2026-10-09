<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Criteria;
use App\Models\Evaluation;
use Illuminate\Database\Eloquent\Factories\Factory;

/*
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /*
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'criteria_id'  => Criteria::factory(),
            'score'        => fake()->randomFloat(2, 1.00, 100.00),
        ];
    }
}
