<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_quotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('quota_limit')->comment('Jumlah penerima beasiswa yang diloloskan');
            $table->string('period')->comment('Periode beasiswa, misal: 2025/2026 Genap');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_quotas');
    }
};
