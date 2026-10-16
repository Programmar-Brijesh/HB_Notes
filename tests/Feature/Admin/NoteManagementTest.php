<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Note;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoteManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AcademicClass $class10;
    private AcademicClass $class12;
    private Subject $math10;
    private Subject $physics12;
    private Chapter $realNumbers10;
    private Chapter $kinematics12;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed foundational classes
        $this->artisan('migrate');

        $this->admin = User::factory()->create([
            'email' => 'admin@hbnotes.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->class10 = AcademicClass::where('class_number', 10)->firstOrFail();
        $this->class12 = AcademicClass::where('class_number', 12)->firstOrFail();

        $this->math10 = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Mathematics',
            'slug' => 'mathematics-10',
            'is_active' => true,
        ]);

        $this->physics12 = Subject::create([
            'academic_class_id' => $this->class12->id,
            'name' => 'Physics',
            'slug' => 'physics-12',
            'is_active' => true,
        ]);

        $this->realNumbers10 = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 1,
            'name' => 'Real Numbers',
            'slug' => 'real-numbers',
            'is_active' => true,
        ]);

        $this->kinematics12 = Chapter::create([
            'subject_id' => $this->physics12->id,
            'chapter_number' => 1,
            'name' => 'Kinematics',
            'slug' => 'kinematics',
            'is_active' => true,
        ]);
    }

    public function test_guests_cannot_access_notes_management(): void
    {
        $this->get(route('admin.notes.index'))->assertRedirect(route('login'));
        $this->get(route('admin.notes.create'))->assertRedirect(route('login'));
        $this->post(route('admin.notes.store'), [])->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_notes_management(): void
    {
        $user = User::factory()->create(['role' => 'student', 'is_active' => true]);

        $this->actingAs($user)->get(route('admin.notes.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('admin.notes.create'))->assertStatus(403);
    }

    public function test_deactivated_admin_cannot_access_notes_management(): void
    {
        $deactivated = User::factory()->create(['role' => 'admin', 'is_active' => false]);

        $this->actingAs($deactivated)->get(route('admin.notes.index'))->assertStatus(403);
    }

    public function test_admin_can_view_notes_index_and_search(): void
    {
        Storage::fake('local');

        $note = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Euclid Division Lemma Notes',
            'slug' => 'euclid-division-lemma-notes',
            'description' => 'Comprehensive formula sheet and proofs',
            'pdf_path' => 'notes/pdf/test-euclid.pdf',
            'file_original_name' => 'euclid-lemma.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.notes.index'));
        $response->assertOk();
        $response->assertSee('Euclid Division Lemma Notes');
        $response->assertSee('Class 10');
        $response->assertSee('Mathematics');

        // Search match
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.notes.index', ['search' => 'Euclid']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Euclid Division Lemma Notes');

        // Search miss
        $missResponse = $this->actingAs($this->admin)->get(route('admin.notes.index', ['search' => 'Thermodynamics']));
        $missResponse->assertOk();
        $missResponse->assertDontSee('Euclid Division Lemma Notes');
    }

    public function test_admin_can_filter_notes_by_class_subject_chapter_and_status(): void
    {
        Storage::fake('local');

        $note10 = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Class 10 Real Numbers Guide',
            'slug' => 'class-10-real-numbers-guide',
            'pdf_path' => 'notes/pdf/note10.pdf',
            'file_original_name' => 'math10.pdf',
            'file_size' => 50000,
            'status' => 'published',
            'is_active' => true,
        ]);

        $note12Draft = Note::create([
            'chapter_id' => $this->kinematics12->id,
            'title' => 'Class 12 Kinematics Draft',
            'slug' => 'class-12-kinematics-draft',
            'pdf_path' => 'notes/pdf/note12.pdf',
            'file_original_name' => 'physics12.pdf',
            'file_size' => 60000,
            'status' => 'draft',
            'is_active' => true,
        ]);

        // Filter by class 10
        $resClass10 = $this->actingAs($this->admin)->get(route('admin.notes.index', ['class_id' => $this->class10->id]));
        $resClass10->assertSee('Class 10 Real Numbers Guide');
        $resClass10->assertDontSee('Class 12 Kinematics Draft');

        // Filter by status: draft
        $resDraft = $this->actingAs($this->admin)->get(route('admin.notes.index', ['status' => 'draft']));
        $resDraft->assertSee('Class 12 Kinematics Draft');
        $resDraft->assertDontSee('Class 10 Real Numbers Guide');

        // Filter by chapter
        $resChapter = $this->actingAs($this->admin)->get(route('admin.notes.index', ['chapter_id' => $this->kinematics12->id]));
        $resChapter->assertSee('Class 12 Kinematics Draft');
        $resChapter->assertDontSee('Class 10 Real Numbers Guide');
    }

    public function test_admin_can_upload_and_create_note_with_valid_pdf(): void
    {
        Storage::fake('local');

        $fakePdf = UploadedFile::fake()->create('real-numbers-revision.pdf', 2048, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('admin.notes.store'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Real Numbers Quick Revision',
            'slug' => 'real-numbers-quick-revision',
            'description' => 'Complete summary of decimal expansions and irrational proofs.',
            'status' => 'published',
            'order_column' => 1,
            'pdf_file' => $fakePdf,
        ]);

        $response->assertRedirect(route('admin.notes.index', ['chapter_id' => $this->realNumbers10->id]));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('notes', [
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Real Numbers Quick Revision',
            'slug' => 'real-numbers-quick-revision',
            'file_original_name' => 'real-numbers-revision.pdf',
            'status' => 'published',
        ]);

        $note = Note::where('title', 'Real Numbers Quick Revision')->firstOrFail();
        $this->assertNotNull($note->pdf_path);
        Storage::disk('local')->assertExists($note->pdf_path);
    }

    public function test_upload_rejects_non_pdf_file(): void
    {
        Storage::fake('local');

        $fakeTxt = UploadedFile::fake()->create('notes.txt', 500, 'text/plain');

        $response = $this->actingAs($this->admin)->from(route('admin.notes.create'))->post(route('admin.notes.store'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Text File Note',
            'status' => 'published',
            'pdf_file' => $fakeTxt,
        ]);

        $response->assertRedirect(route('admin.notes.create'));
        $response->assertSessionHasErrors('pdf_file');
        $this->assertDatabaseMissing('notes', ['title' => 'Text File Note']);
    }

    public function test_upload_rejects_oversized_pdf(): void
    {
        Storage::fake('local');

        config(['hbnotes.max_pdf_size_kb' => 1024]); // 1MB limit for testing

        $largePdf = UploadedFile::fake()->create('huge-book.pdf', 2048, 'application/pdf'); // 2MB

        $response = $this->actingAs($this->admin)->from(route('admin.notes.create'))->post(route('admin.notes.store'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Huge Book Note',
            'status' => 'published',
            'pdf_file' => $largePdf,
        ]);

        $response->assertRedirect(route('admin.notes.create'));
        $response->assertSessionHasErrors('pdf_file');
        $this->assertDatabaseMissing('notes', ['title' => 'Huge Book Note']);
    }

    public function test_hierarchy_validation_rejects_mismatched_chapter_and_subject(): void
    {
        Storage::fake('local');

        $fakePdf = UploadedFile::fake()->create('test.pdf', 500, 'application/pdf');

        // Chapter is realNumbers10 (belongs to Math 10), but subject_id passed is physics12
        $response = $this->actingAs($this->admin)->from(route('admin.notes.create'))->post(route('admin.notes.store'), [
            'class_id' => $this->class12->id,
            'subject_id' => $this->physics12->id,
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Mismatched Note',
            'status' => 'published',
            'pdf_file' => $fakePdf,
        ]);

        $response->assertRedirect(route('admin.notes.create'));
        $response->assertSessionHasErrors('chapter_id');
        $this->assertDatabaseMissing('notes', ['title' => 'Mismatched Note']);
    }

    public function test_admin_can_update_note_metadata_without_uploading_new_file(): void
    {
        Storage::fake('local');

        $pdfPath = 'notes/pdf/existing-doc.pdf';
        Storage::disk('local')->put($pdfPath, 'dummy pdf content');

        $note = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Original Title',
            'slug' => 'original-title',
            'pdf_path' => $pdfPath,
            'file_original_name' => 'original.pdf',
            'file_size' => 1024,
            'status' => 'draft',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.notes.update', $note), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Updated Note Title',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.notes.index', ['chapter_id' => $this->realNumbers10->id]));
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated Note Title',
            'status' => 'published',
            'pdf_path' => $pdfPath, // Unchanged
        ]);
        Storage::disk('local')->assertExists($pdfPath);
    }

    public function test_file_replacement_deletes_old_file_and_stores_new_file(): void
    {
        Storage::fake('local');

        $oldPath = 'notes/pdf/old-document.pdf';
        Storage::disk('local')->put($oldPath, 'old pdf content');

        $note = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Chapter 1 Master Notes',
            'slug' => 'chapter-1-master-notes',
            'pdf_path' => $oldPath,
            'file_original_name' => 'v1.pdf',
            'file_size' => 500,
            'status' => 'published',
            'is_active' => true,
        ]);

        Storage::disk('local')->assertExists($oldPath);

        $newPdf = UploadedFile::fake()->create('v2-revised.pdf', 1500, 'application/pdf');

        $response = $this->actingAs($this->admin)->put(route('admin.notes.update', $note), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Chapter 1 Master Notes (v2)',
            'status' => 'published',
            'pdf_file' => $newPdf,
        ]);

        $response->assertRedirect(route('admin.notes.index', ['chapter_id' => $this->realNumbers10->id]));

        $note->refresh();
        $this->assertEquals('Chapter 1 Master Notes (v2)', $note->title);
        $this->assertEquals('v2-revised.pdf', $note->file_original_name);
        $this->assertNotEquals($oldPath, $note->pdf_path);

        // Old file must be deleted from storage
        Storage::disk('local')->assertMissing($oldPath);

        // New file must exist on storage
        Storage::disk('local')->assertExists($note->pdf_path);
    }

    public function test_note_deletion_removes_database_record_and_cleans_up_storage(): void
    {
        Storage::fake('local');

        $pdfPath = 'notes/pdf/to-be-deleted.pdf';
        Storage::disk('local')->put($pdfPath, 'sample content to delete');

        $note = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Temporary Note',
            'slug' => 'temporary-note',
            'pdf_path' => $pdfPath,
            'file_original_name' => 'temp.pdf',
            'file_size' => 100,
            'status' => 'draft',
            'is_active' => true,
        ]);

        Storage::disk('local')->assertExists($pdfPath);

        $response = $this->actingAs($this->admin)->delete(route('admin.notes.destroy', $note));
        $response->assertRedirect(route('admin.notes.index', ['chapter_id' => $this->realNumbers10->id]));

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
        Storage::disk('local')->assertMissing($pdfPath);
    }

    public function test_admin_can_download_and_preview_note(): void
    {
        Storage::fake('local');

        $pdfPath = 'notes/pdf/downloadable.pdf';
        Storage::disk('local')->put($pdfPath, 'sample binary pdf stream');

        $note = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Downloadable Study Note',
            'slug' => 'downloadable-study-note',
            'pdf_path' => $pdfPath,
            'file_original_name' => 'final-study-guide.pdf',
            'file_size' => 1000,
            'status' => 'published',
            'download_count' => 0,
            'is_active' => true,
        ]);

        // Test Download
        $downloadRes = $this->actingAs($this->admin)->get(route('admin.notes.download', $note));
        $downloadRes->assertOk();
        $this->assertEquals(1, $note->fresh()->download_count);

        // Test Preview
        $previewRes = $this->actingAs($this->admin)->get(route('admin.notes.preview', $note));
        $previewRes->assertOk();
        $previewRes->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_cannot_delete_chapter_with_dependent_notes(): void
    {
        Storage::fake('local');

        $note = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Locked Note',
            'slug' => 'locked-note',
            'pdf_path' => 'notes/pdf/locked.pdf',
            'file_original_name' => 'locked.pdf',
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.chapters.destroy', $this->realNumbers10));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('chapters', ['id' => $this->realNumbers10->id]);
    }

    public function test_note_scopes_and_hierarchy_traversal(): void
    {
        Storage::fake('local');

        $published = Note::create([
            'chapter_id' => $this->realNumbers10->id,
            'title' => 'Published Note',
            'slug' => 'published-note',
            'pdf_path' => 'notes/pdf/pub.pdf',
            'file_original_name' => 'pub.pdf',
            'status' => 'published',
            'is_active' => true,
        ]);

        $draft = Note::create([
            'chapter_id' => $this->kinematics12->id,
            'title' => 'Draft Note',
            'slug' => 'draft-note',
            'pdf_path' => 'notes/pdf/draft.pdf',
            'file_original_name' => 'draft.pdf',
            'status' => 'draft',
            'is_active' => true,
        ]);

        $this->assertTrue($published->isPublished());
        $this->assertFalse($draft->isPublished());
        $this->assertTrue($draft->isDraft());

        $this->assertEquals(1, Note::published()->count());
        $this->assertEquals(1, Note::draft()->count());

        // Derived academic relationships
        $this->assertEquals('Mathematics', $published->subject->name);
        $this->assertEquals('Class 10', $published->academic_class->name);
        $this->assertEquals('Physics', $draft->subject->name);
        $this->assertEquals('Class 12', $draft->academic_class->name);
    }
}
