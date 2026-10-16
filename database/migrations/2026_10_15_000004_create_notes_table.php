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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('restrict');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('pdf_path', 500);
            $table->string('file_original_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100)->default('application/pdf');
            $table->string('status', 20)->default('published'); // draft, published
            $table->string('thumbnail_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order_column')->default(0);
            $table->unsignedBigInteger('download_count')->default(0);
            $table->timestamps();

            $table->index(['chapter_id', 'status']);
            $table->index('status');
            $table->index('is_active');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
