<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);              // mis. "Gelombang 1 — 2025/2026"
            $table->unsignedSmallInteger('kuota')->default(5);
            $table->boolean('aktif')->default(false); // hanya satu periode aktif
            $table->timestamps();
        });

        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();     // C1..C6
            $table->string('nama', 100);              // Rata-rata rapor, dll.
            $table->enum('tipe', ['benefit', 'cost']);
            $table->string('petunjuk', 255)->nullable(); // hint skala input
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->string('kode', 10);               // A001..A011
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique(['period_id', 'kode']);
        });

        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alternative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained()->cascadeOnDelete();
            $table->decimal('nilai', 14, 8);          // nilai hasil konversi skala (Xij)
            $table->string('catatan', 255)->nullable(); // jawaban asli form (pelacakan)
            $table->timestamps();
            $table->unique(['alternative_id', 'criterion_id']);
        });

        // Perbandingan berpasangan AHP (segitiga atas, a < b menurut urutan kriteria)
        Schema::create('comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criterion_a_id')->constrained('criteria')->cascadeOnDelete();
            $table->foreignId('criterion_b_id')->constrained('criteria')->cascadeOnDelete();
            $table->decimal('nilai', 12, 8);          // skala Saaty 1-9 (boleh pecahan 1/x)
            $table->timestamps();
            $table->unique(['period_id', 'criterion_a_id', 'criterion_b_id']);
        });

        Schema::create('result_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->decimal('lambda_max', 14, 6);
            $table->decimal('ci', 14, 6);
            $table->decimal('cr', 14, 6);
            $table->boolean('konsisten');
            $table->json('weights')->nullable(); // snapshot bobot Wj saat perhitungan
            $table->unsignedInteger('jumlah_alternatif');
            $table->timestamps();
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('result_batches')->cascadeOnDelete();
            $table->foreignId('alternative_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kode', 10);               // snapshot utk riwayat
            $table->string('nama', 100);              // snapshot utk riwayat
            $table->decimal('si', 16, 8);
            $table->decimal('vi', 12, 8);
            $table->unsignedSmallInteger('rank');
            $table->boolean('rekomendasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
        Schema::dropIfExists('result_batches');
        Schema::dropIfExists('comparisons');
        Schema::dropIfExists('scores');
        Schema::dropIfExists('alternatives');
        Schema::dropIfExists('criteria');
        Schema::dropIfExists('periods');
    }
};
