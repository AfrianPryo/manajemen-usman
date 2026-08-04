<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Buat Tabel Unit Usaha
        Schema::create('unit_usahas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->unique(); // Misal: KOPSIS, KANTIN
            $table->text('deskripsi')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        // 2. Tambahkan unit_usaha_id ke tabel-tabel vital
        $tables = [
            'users', 'products', 'categories', 
            'finance_categories', 'finance_transactions', 'stock_movements'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('unit_usaha_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('unit_usahas')
                    ->cascadeOnDelete(); // Atau nullOnDelete sesuai kebijakan
            });
        }
    }
};
