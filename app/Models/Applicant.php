<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ApplicantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Applicant extends Model
{
    /** @use HasFactory<ApplicantFactory> */
    use HasFactory;
    protected $fillable = [
        'nim',
        'name',
        'study_program',
    ];

    /** @return HasMany<Evaluation> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Cek apakah semua kriteria sudah diisi nilainya.
     *
     * @param \Illuminate\Support\Collection<int, Criteria> $criterias
     */
    public function hasCompleteEvaluations(\Illuminate\Support\Collection $criterias): bool
    {
        $evaluatedIds = $this->evaluations->pluck('criteria_id')->sort()->values();
        $requiredIds  = $criterias->pluck('id')->sort()->values();

        return $evaluatedIds->toArray() === $requiredIds->toArray();
    }
}
