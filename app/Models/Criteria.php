<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CriteriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criteria extends Model
{
    /** @use HasFactory<CriteriaFactory> */
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'weight',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'float',
        ];
    }

    /** @return HasMany<Evaluation> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function isBenefit(): bool
    {
        return $this->type === 'benefit';
    }

    public function isCost(): bool
    {
        return $this->type === 'cost';
    }
}
