<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Kode singkat kriteria, misal: C1, C2');
            $table->string('name');
            $table->decimal('weight', 4, 2)->comment('Bobot antara 0.01 - 1.00, total semua harus = 1.00');
            $table->enum('type', ['benefit', 'cost'])->comment('benefit = nilai besar lebih baik; cost = nilai kecil lebih baik');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterias');
    }
};
