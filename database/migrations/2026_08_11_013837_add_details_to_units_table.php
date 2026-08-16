<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'category')) {
                $table->enum('category', ['ritel', 'jasa'])->default('ritel')->after('department');
            }
            if (!Schema::hasColumn('units', 'pic_name')) {
                $table->string('pic_name')->nullable()->after('category');
            }
            if (!Schema::hasColumn('units', 'phone')) {
                $table->string('phone', 20)->nullable()->after('pic_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $columnsToDrop = array_filter(['category', 'pic_name', 'phone'], function ($column) {
                return Schema::hasColumn('units', $column);
            });

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};