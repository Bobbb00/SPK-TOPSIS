<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ScholarshipQuota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@spk.test'],
            [
                'name' => 'Panitia Seleksi',
                'password' => 'password',
            ]
        );

        ScholarshipQuota::firstOrCreate(
            ['period' => '2026/2027'],
            [
                'quota_limit' => 5,
            ]
        );

        $this->call([
            CriteriaSeeder::class,
            ApplicantSeeder::class,
        ]);
    }
}
