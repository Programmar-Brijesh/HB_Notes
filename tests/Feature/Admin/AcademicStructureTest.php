<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);
    }

    /* -------------------------------------------------------------------------
     | Authorization & Route Protection
     | ------------------------------------------------------------------------- */

    public function test_guests_cannot_access_academic_structure_routes(): void
    {
        $this->get('/admin/classes')->assertRedirect('/login');
        $this->get('/admin/subjects')->assertRedirect('/login');
        $this->get('/admin/chapters')->assertRedirect('/login');
        $this->post('/admin/classes', [])->assertRedirect('/login');
    }

    public function test_non_admin_users_cannot_access_academic_structure_routes(): void
    {
        $this->actingAs($this->student)->get('/admin/classes')->assertStatus(403);
        $this->actingAs($this->student)->get('/admin/subjects')->assertStatus(403);
        $this->actingAs($this->student)->get('/admin/chapters')->assertStatus(403);
    }

    public function test_deactivated_admin_cannot_access_academic_structure(): void
    {
        $deactivated = User::factory()->create(['role' => 'admin', 'is_active' => false]);

        $this->actingAs($deactivated)->get('/admin/classes')->assertStatus(403);
    }

    /* -------------------------------------------------------------------------
     | Academic Classes CRUD & Validation
     | ------------------------------------------------------------------------- */

    public function test_admin_can_view_classes_index_and_search(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/classes');

        $response->assertStatus(200);
        $response->assertSee('Class 9');
        $response->assertSee('Class 10');
        $response->assertSee('Class 11');
        $response->assertSee('Class 12');

        $searchResponse = $this->actingAs($this->admin)->get('/admin/classes?search=Class 10');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Class 10');
    }

    public function test_admin_can_create_new_academic_class(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/classes', [
            'name' => 'Foundation Tier',
            'class_number' => 8,
            'stream' => 'Middle School',
            'description' => 'Bridge course curriculum.',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/classes');
        $this->assertDatabaseHas('academic_classes', [
            'name' => 'Foundation Tier',
            'class_number' => 8,
            'slug' => 'foundation-tier',
        ]);
    }

    public function test_duplicate_class_number_or_name_is_rejected(): void
    {
        // Class 9 already exists from migration seed
        $response = $this->actingAs($this->admin)->post('/admin/classes', [
            'name' => 'Duplicate Nine',
            'class_number' => 9, // Duplicate class_number
        ]);

        $response->assertSessionHasErrors('class_number');

        $nameResponse = $this->actingAs($this->admin)->post('/admin/classes', [
            'name' => 'Class 9', // Duplicate name
            'class_number' => 15,
        ]);

        $nameResponse->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_academic_class(): void
    {
        $class9 = AcademicClass::where('class_number', 9)->firstOrFail();

        $response = $this->actingAs($this->admin)->put("/admin/classes/{$class9->id}", [
            'name' => 'Class 9 (Updated)',
            'class_number' => 9,
            'stream' => 'Secondary Revised',
            'description' => 'Updated syllabus description.',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/classes');
        $this->assertDatabaseHas('academic_classes', [
            'id' => $class9->id,
            'name' => 'Class 9 (Updated)',
            'stream' => 'Secondary Revised',
        ]);
    }

    public function test_cannot_delete_class_with_dependent_subjects(): void
    {
        $class10 = AcademicClass::where('class_number', 10)->firstOrFail();

        // Create dependent subject
        Subject::create([
            'academic_class_id' => $class10->id,
            'name' => 'Mathematics',
            'code' => 'MATH-10',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/classes/{$class10->id}");

        $response->assertRedirect('/admin/classes');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('academic_classes', ['id' => $class10->id]);
    }

    public function test_can_delete_class_without_subjects(): void
    {
        $newClass = AcademicClass::create([
            'name' => 'Class 5 Temporary',
            'class_number' => 5,
            'slug' => 'class-5-temporary',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/classes/{$newClass->id}");

        $response->assertRedirect('/admin/classes');
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('academic_classes', ['id' => $newClass->id]);
    }

    /* -------------------------------------------------------------------------
     | Subjects CRUD & Validation
     | ------------------------------------------------------------------------- */

    public function test_admin_can_create_subject_under_class(): void
    {
        $class11 = AcademicClass::where('class_number', 11)->firstOrFail();

        $response = $this->actingAs($this->admin)->post('/admin/subjects', [
            'academic_class_id' => $class11->id,
            'name' => 'Physics',
            'code' => 'PHY-042',
            'description' => 'Core Physics syllabus for Class 11.',
            'order_column' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/subjects?class_id=' . $class11->id);
        $this->assertDatabaseHas('subjects', [
            'academic_class_id' => $class11->id,
            'name' => 'Physics',
            'code' => 'PHY-042',
            'slug' => 'physics',
        ]);
    }

    public function test_duplicate_subject_in_same_class_is_rejected(): void
    {
        $class12 = AcademicClass::where('class_number', 12)->firstOrFail();

        Subject::create([
            'academic_class_id' => $class12->id,
            'name' => 'Chemistry',
            'code' => 'CHEM-12',
            'is_active' => true,
        ]);

        // Attempt creating duplicate Chemistry in Class 12
        $response = $this->actingAs($this->admin)->post('/admin/subjects', [
            'academic_class_id' => $class12->id,
            'name' => 'Chemistry',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_same_subject_name_in_different_classes_is_allowed(): void
    {
        $class9 = AcademicClass::where('class_number', 9)->firstOrFail();
        $class10 = AcademicClass::where('class_number', 10)->firstOrFail();

        Subject::create([
            'academic_class_id' => $class9->id,
            'name' => 'English Literature',
            'is_active' => true,
        ]);

        // Same name under Class 10 should succeed
        $response = $this->actingAs($this->admin)->post('/admin/subjects', [
            'academic_class_id' => $class10->id,
            'name' => 'English Literature',
        ]);

        $response->assertRedirect('/admin/subjects?class_id=' . $class10->id);
        $this->assertDatabaseHas('subjects', [
            'academic_class_id' => $class10->id,
            'name' => 'English Literature',
        ]);
    }

    public function test_cannot_delete_subject_with_dependent_chapters(): void
    {
        $class9 = AcademicClass::where('class_number', 9)->firstOrFail();
        $subject = Subject::create([
            'academic_class_id' => $class9->id,
            'name' => 'Biology',
            'is_active' => true,
        ]);

        Chapter::create([
            'subject_id' => $subject->id,
            'chapter_number' => 1,
            'name' => 'The Fundamental Unit of Life',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/subjects/{$subject->id}");

        $response->assertRedirect('/admin/subjects');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    }

    /* -------------------------------------------------------------------------
     | Chapters CRUD & Validation
     | ------------------------------------------------------------------------- */

    public function test_admin_can_create_and_manage_chapters(): void
    {
        $class10 = AcademicClass::where('class_number', 10)->firstOrFail();
        $subject = Subject::create([
            'academic_class_id' => $class10->id,
            'name' => 'Mathematics',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/chapters', [
            'subject_id' => $subject->id,
            'chapter_number' => 1,
            'name' => 'Real Numbers',
            'description' => 'Euclid division lemma and fundamental theorem of arithmetic.',
            'order_column' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/chapters?subject_id=' . $subject->id);
        $this->assertDatabaseHas('chapters', [
            'subject_id' => $subject->id,
            'chapter_number' => 1,
            'name' => 'Real Numbers',
            'slug' => 'real-numbers',
        ]);

        // Duplicate chapter number under same subject rejected
        $dupResponse = $this->actingAs($this->admin)->post('/admin/chapters', [
            'subject_id' => $subject->id,
            'chapter_number' => 1,
            'name' => 'Polynomials',
        ]);
        $dupResponse->assertSessionHasErrors('chapter_number');

        // Can delete chapter
        $chapter = Chapter::where('subject_id', $subject->id)->where('chapter_number', 1)->firstOrFail();
        $delResponse = $this->actingAs($this->admin)->delete("/admin/chapters/{$chapter->id}");
        $delResponse->assertRedirect('/admin/chapters?subject_id=' . $subject->id);
        $this->assertDatabaseMissing('chapters', ['id' => $chapter->id]);
    }

    /* -------------------------------------------------------------------------
     | Hierarchy & Model Relationships
     | ------------------------------------------------------------------------- */

    public function test_academic_hierarchy_relationships(): void
    {
        $class = AcademicClass::where('class_number', 11)->firstOrFail();

        $subject = Subject::create([
            'academic_class_id' => $class->id,
            'name' => 'Chemistry XI',
            'is_active' => true,
        ]);

        $chapter = Chapter::create([
            'subject_id' => $subject->id,
            'chapter_number' => 1,
            'name' => 'Some Basic Concepts of Chemistry',
            'is_active' => true,
        ]);

        // Verify Class -> Subjects
        $this->assertTrue($class->subjects->contains($subject));

        // Verify Subject -> Class
        $this->assertEquals($class->id, $subject->academicClass->id);

        // Verify Subject -> Chapters
        $this->assertTrue($subject->chapters->contains($chapter));

        // Verify Chapter -> Subject
        $this->assertEquals($subject->id, $chapter->subject->id);

        // Verify Class -> Chapters (HasManyThrough)
        $this->assertTrue($class->chapters->contains($chapter));
    }
}
