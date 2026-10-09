<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipQuota extends Model
{
    protected $fillable = [
        'quota_limit',
        'period',
    ];

    protected function casts(): array
    {
        return [
            'quota_limit' => 'integer',
        ];
    }

    /**
     * Ambil kuota aktif terbaru (satu record terakhir).
     */
    public static function active(): ?self
    {
        return self::latest()->first();
    }
}
