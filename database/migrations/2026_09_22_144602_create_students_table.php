<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('jenis_kelamin')->nullable(); // Laki-laki, Perempuan
            $table->string('prodi')->nullable(); // Manajemen, Akuntansi, Bisnis Digital
            $table->string('konsentrasi')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->decimal('mitra_score', 5, 2)->nullable();
            $table->decimal('dpl_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('letter_grade', 5)->nullable();
            $table->string('status')->default('draft'); // draft, locked
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
