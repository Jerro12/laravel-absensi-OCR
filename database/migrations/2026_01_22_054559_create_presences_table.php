<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            // Ini trik Polymorphic: otomatis bikin kolom 'user_id' (bigint) dan 'user_type' (string)
            $table->morphs('user');

            $table->date('date');
            $table->time('check_in');
            $table->time('check_out')->nullable();

            $table->string('image_capture'); // Foto bukti
            $table->string('detected_name')->nullable(); // Nama hasil OCR
            $table->float('face_score')->default(0); // Nilai kecocokan

            $table->string('status')->default('valid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
