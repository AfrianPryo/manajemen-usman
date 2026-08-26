<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            // null jika numbering_reset = 'yearly' atau 'never'
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['document_template_id', 'year', 'month'], 'doc_seq_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_number_sequences');
    }
};
