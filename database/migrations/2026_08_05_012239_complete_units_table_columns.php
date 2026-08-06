<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'department')) {
                $table->string('department')->default('PPLG')->after('slug');
            }
            if (!Schema::hasColumn('units', 'category')) {
                $table->string('category')->default('ritel')->after('department');
            }
            if (!Schema::hasColumn('units', 'pic_name')) {
                $table->string('pic_name')->nullable()->after('category');
            }
            if (!Schema::hasColumn('units', 'phone')) {
                $table->string('phone')->nullable()->after('pic_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['department', 'category', 'pic_name', 'phone']);
        });
    }
};