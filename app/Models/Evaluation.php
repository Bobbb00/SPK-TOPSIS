<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EvaluationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    /** @use HasFactory<EvaluationFactory> */
    use HasFactory;
    protected $fillable = [
        'applicant_id',
        'criteria_id',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
        ];
    }

    /** @return BelongsTo<Applicant, Evaluation> */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /** @return BelongsTo<Criteria, Evaluation> */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class);
    }
}
