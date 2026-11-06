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
        Schema::table('laporans', function (Blueprint $table) {
            $table->text('tanggapan_admin')->nullable();
        });

        Schema::create('laporan_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporans')->cascadeOnDelete();
            $table->string('previous_status', 32);
            $table->string('new_status', 32);
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['laporan_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_status_histories');

        Schema::table('laporans', function (Blueprint $table) {
            $table->dropColumn('tanggapan_admin');
        });
    }
};
