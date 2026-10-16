<?php

namespace Tests\Feature\Api\V1;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureApiTest extends TestCase
{
    use RefreshDatabase;

    private AcademicClass $class10;
    private AcademicClass $inactiveClass;
    private Subject $math10;
    private Subject $science10;
    private Subject $inactiveSubject;
    private Chapter $realNumbers;
    private Chapter $polynomials;
    private Chapter $inactiveChapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        $this->class10 = AcademicClass::where('class_number', 10)->firstOrFail();

        // Create an inactive class
        $this->inactiveClass = AcademicClass::create([
            'name' => 'Class 8 (Archived)',
            'slug' => 'class-8-archived',
            'class_number' => 8,
            'is_active' => false,
        ]);

        // Subjects for class 10
        $this->math10 = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Mathematics',
            'code' => 'MATH-10',
            'slug' => 'mathematics-10',
            'order_column' => 1,
            'is_active' => true,
        ]);

        $this->science10 = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Science',
            'code' => 'SCI-10',
            'slug' => 'science-10',
            'order_column' => 2,
            'is_active' => true,
        ]);

        $this->inactiveSubject = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Advanced Sanskrit (Draft)',
            'code' => 'SKT-10',
            'slug' => 'advanced-sanskrit-draft',
            'order_column' => 3,
            'is_active' => false,
        ]);

        // Chapters for Mathematics
        $this->realNumbers = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 1,
            'name' => 'Real Numbers',
            'slug' => 'real-numbers',
            'order_column' => 1,
            'is_active' => true,
        ]);

        $this->polynomials = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 2,
            'name' => 'Polynomials',
            'slug' => 'polynomials',
            'order_column' => 2,
            'is_active' => true,
        ]);

        $this->inactiveChapter = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 99,
            'name' => 'Old Syllabus Archive',
            'slug' => 'old-syllabus-archive',
            'order_column' => 99,
            'is_active' => false,
        ]);
    }

    public function test_can_fetch_published_academic_classes(): void
    {
        $response = $this->getJson('/api/v1/classes');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'slug', 'class_number', 'stream', 'description', 'subjects_count'],
                ],
            ]);

        // Active class 10 should be present; inactive class 8 should NOT be present
        $classIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($this->class10->id, $classIds);
        $this->assertNotContains($this->inactiveClass->id, $classIds);
    }

    public function test_can_fetch_single_active_class_and_inactive_returns_404(): void
    {
        // Active class
        $this->getJson("/api/v1/classes/{$this->class10->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->class10->id)
            ->assertJsonPath('data.name', 'Class 10');

        // Inactive class returns 404
        $this->getJson("/api/v1/classes/{$this->inactiveClass->id}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);

        // Non-existent class returns 404
        $this->getJson('/api/v1/classes/99999')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_can_fetch_subjects_for_a_class(): void
    {
        $response = $this->getJson("/api/v1/classes/{$this->class10->id}/subjects");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'academic_class_id', 'name', 'code', 'slug', 'order_column', 'chapters_count'],
                ],
            ]);

        $subjectNames = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('Mathematics', $subjectNames);
        $this->assertContains('Science', $subjectNames);
        $this->assertNotContains('Advanced Sanskrit (Draft)', $subjectNames);
    }

    public function test_fetching_subjects_for_inactive_class_returns_404(): void
    {
        $this->getJson("/api/v1/classes/{$this->inactiveClass->id}/subjects")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_can_fetch_subject_list_filtered_by_class_id(): void
    {
        $response = $this->getJson("/api/v1/subjects?class_id={$this->class10->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $subjectIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($this->math10->id, $subjectIds);
        $this->assertNotContains($this->inactiveSubject->id, $subjectIds);
    }

    public function test_can_fetch_single_active_subject_and_inactive_returns_404(): void
    {
        $this->getJson("/api/v1/subjects/{$this->math10->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->math10->id)
            ->assertJsonPath('data.name', 'Mathematics');

        $this->getJson("/api/v1/subjects/{$this->inactiveSubject->id}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_can_fetch_chapters_for_a_subject(): void
    {
        $response = $this->getJson("/api/v1/subjects/{$this->math10->id}/chapters");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'subject_id', 'chapter_number', 'name', 'slug', 'order_column', 'notes_count', 'mcqs_count'],
                ],
            ]);

        $chapterNames = collect($response->json('data'))->pluck('name')->all();
        $this->assertContains('Real Numbers', $chapterNames);
        $this->assertContains('Polynomials', $chapterNames);
        $this->assertNotContains('Old Syllabus Archive', $chapterNames);
    }

    public function test_fetching_chapters_for_inactive_subject_returns_404(): void
    {
        $this->getJson("/api/v1/subjects/{$this->inactiveSubject->id}/chapters")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_can_fetch_single_active_chapter(): void
    {
        $this->getJson("/api/v1/chapters/{$this->realNumbers->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->realNumbers->id)
            ->assertJsonPath('data.name', 'Real Numbers');

        $this->getJson("/api/v1/chapters/{$this->inactiveChapter->id}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
