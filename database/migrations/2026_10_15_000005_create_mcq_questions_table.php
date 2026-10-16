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
        Schema::create('mcq_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('restrict');
            $table->text('question');
            $table->char('question_hash', 40); // SHA-1 of normalized text for fast duplicate indexing
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->char('correct_option', 1); // A, B, C, D
            $table->text('explanation')->nullable();
            $table->string('difficulty', 20)->default('medium'); // easy, medium, hard
            $table->string('status', 20)->default('published'); // draft, published
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();

            $table->index(['chapter_id', 'question_hash']);
            $table->index(['chapter_id', 'status']);
            $table->index('difficulty');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcq_questions');
    }
};
