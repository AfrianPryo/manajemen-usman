<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('password')
                  ->constrained('units')
                  ->nullOnDelete();
            $table->string('nip')->nullable()->unique()->after('unit_id');
            $table->string('phone')->nullable()->after('nip');
            $table->string('employee_status')->nullable()->after('phone'); // Guru, Pegawai, Siswa
            $table->string('profile_photo_path')->nullable()->after('employee_status');
            $table->boolean('is_active')->default(true)->after('profile_photo_path');
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->softDeletes()->after('last_login_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn([
                'unit_id', 'nip', 'phone', 'employee_status',
                'profile_photo_path', 'is_active', 'must_change_password',
                'last_login_at', 'last_login_ip', 'deleted_at'
            ]);
        });
    }
};