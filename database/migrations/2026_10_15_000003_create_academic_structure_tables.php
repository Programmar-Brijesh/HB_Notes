<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('class_number')->unique();
            $table->string('stream')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->onDelete('restrict');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();

            $table->unique(['academic_class_id', 'name']);
        });

        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('restrict');
            $table->unsignedInteger('chapter_number');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();

            $table->unique(['subject_id', 'chapter_number']);
        });

        // Seed foundational classes 9, 10, 11, and 12 without duplication
        $foundationalClasses = [
            [
                'name' => 'Class 9',
                'slug' => 'class-9',
                'class_number' => 9,
                'stream' => 'Secondary (General)',
                'description' => 'Foundational secondary syllabus across Mathematics, Science & Social Sciences.',
                'is_active' => true,
            ],
            [
                'name' => 'Class 10',
                'slug' => 'class-10',
                'class_number' => 10,
                'stream' => 'Secondary (Board Exam)',
                'description' => 'Comprehensive Board Examination preparation curriculum.',
                'is_active' => true,
            ],
            [
                'name' => 'Class 11',
                'slug' => 'class-11',
                'class_number' => 11,
                'stream' => 'Senior Secondary',
                'description' => 'Senior Secondary concepts across Science, Commerce and Humanities streams.',
                'is_active' => true,
            ],
            [
                'name' => 'Class 12',
                'slug' => 'class-12',
                'class_number' => 12,
                'stream' => 'Senior Secondary (Board Exam)',
                'description' => 'Senior Secondary Board syllabus and Competitive Entrance foundation.',
                'is_active' => true,
            ],
        ];

        foreach ($foundationalClasses as $item) {
            DB::table('academic_classes')->updateOrInsert(
                ['class_number' => $item['class_number']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_classes');
    }
};
