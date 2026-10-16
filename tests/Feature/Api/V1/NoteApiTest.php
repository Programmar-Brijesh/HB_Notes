<?php

namespace Tests\Feature\Api\V1;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Note;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    private AcademicClass $class10;
    private Subject $math10;
    private Subject $otherSubject;
    private Chapter $realNumbers;
    private Chapter $emptyChapter;
    private Note $publishedNote;
    private Note $draftNote;
    private Note $inactiveNote;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        Storage::fake(config('hbnotes.pdf_storage_disk', 'local'));

        $this->class10 = AcademicClass::where('class_number', 10)->firstOrFail();

        $this->math10 = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Mathematics',
            'slug' => 'mathematics-10',
            'is_active' => true,
        ]);

        $this->otherSubject = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Social Science',
            'slug' => 'social-science-10',
            'is_active' => true,
        ]);

        $this->realNumbers = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 1,
            'name' => 'Real Numbers',
            'slug' => 'real-numbers',
            'is_active' => true,
        ]);

        $this->emptyChapter = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 2,
            'name' => 'Polynomials',
            'slug' => 'polynomials',
            'is_active' => true,
        ]);

        // Create mock PDF and mock thumbnail
        $disk = config('hbnotes.pdf_storage_disk', 'local');
        $pdfPath = 'notes/pdf/mock_sample_real_numbers.pdf';
        $thumbPath = 'notes/thumbnails/mock_thumb_real_numbers.jpg';
        Storage::disk($disk)->put($pdfPath, '%PDF-1.4 mock content for real numbers notes');
        Storage::disk($disk)->put($thumbPath, 'mock image data');

        $this->publishedNote = Note::create([
            'chapter_id' => $this->realNumbers->id,
            'title' => 'Real Numbers Complete Theory',
            'slug' => 'real-numbers-complete-theory',
            'description' => 'Comprehensive chapter notes covering Euclid division lemma and rational numbers.',
            'pdf_path' => $pdfPath,
            'file_original_name' => 'Real_Numbers_Theory.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'status' => 'published',
            'thumbnail_path' => $thumbPath,
            'is_active' => true,
            'order_column' => 1,
            'download_count' => 5,
        ]);

        $this->draftNote = Note::create([
            'chapter_id' => $this->realNumbers->id,
            'title' => 'Internal Teacher Draft Notes',
            'slug' => 'internal-teacher-draft-notes',
            'description' => 'Unpublished internal review draft.',
            'pdf_path' => $pdfPath,
            'file_original_name' => 'Draft_Notes.pdf',
            'file_size' => 51200,
            'mime_type' => 'application/pdf',
            'status' => 'draft',
            'is_active' => true,
            'order_column' => 2,
        ]);

        $this->inactiveNote = Note::create([
            'chapter_id' => $this->realNumbers->id,
            'title' => 'Deactivated Outdated Notes',
            'slug' => 'deactivated-outdated-notes',
            'pdf_path' => $pdfPath,
            'file_original_name' => 'Outdated.pdf',
            'file_size' => 20480,
            'mime_type' => 'application/pdf',
            'status' => 'published',
            'is_active' => false,
            'order_column' => 3,
        ]);
    }

    public function test_can_fetch_published_notes_for_chapter_excluding_draft_and_inactive(): void
    {
        $response = $this->getJson("/api/v1/chapters/{$this->realNumbers->id}/notes");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'chapter_id',
                        'title',
                        'slug',
                        'description',
                        'file_name',
                        'file_size',
                        'formatted_file_size',
                        'mime_type',
                        'download_count',
                        'order_column',
                        'has_thumbnail',
                        'preview_url',
                        'download_url',
                        'thumbnail_url',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        $noteIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($this->publishedNote->id, $noteIds);
        $this->assertNotContains($this->draftNote->id, $noteIds);
        $this->assertNotContains($this->inactiveNote->id, $noteIds);

        // Security check: internal pdf_path storage path must never be exposed
        $rawJson = $response->getContent();
        $this->assertStringNotContainsString('notes/pdf/', $rawJson);
    }

    public function test_empty_notes_chapter_returns_empty_array_with_200(): void
    {
        $response = $this->getJson("/api/v1/chapters/{$this->emptyChapter->id}/notes");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0);
    }

    public function test_hierarchy_validation_rejects_mismatched_subject_and_chapter(): void
    {
        // $this->realNumbers belongs to $this->math10, NOT $this->otherSubject
        $response = $this->getJson("/api/v1/notes?chapter_id={$this->realNumbers->id}&subject_id={$this->otherSubject->id}");

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The selected chapter does not belong to the chosen subject');
    }

    public function test_can_fetch_note_details_with_hierarchy_metadata(): void
    {
        $response = $this->getJson("/api/v1/notes/{$this->publishedNote->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->publishedNote->id)
            ->assertJsonPath('data.title', 'Real Numbers Complete Theory')
            ->assertJsonPath('data.hierarchy.class.class_number', 10)
            ->assertJsonPath('data.hierarchy.subject.name', 'Mathematics')
            ->assertJsonPath('data.hierarchy.chapter.name', 'Real Numbers');
    }

    public function test_draft_or_inactive_note_details_returns_404(): void
    {
        $this->getJson("/api/v1/notes/{$this->draftNote->id}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);

        $this->getJson("/api/v1/notes/{$this->inactiveNote->id}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_secure_preview_streams_pdf_inline_with_headers(): void
    {
        $response = $this->get("/api/v1/notes/{$this->publishedNote->id}/preview");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_secure_download_streams_pdf_and_increments_download_count(): void
    {
        $initialCount = $this->publishedNote->download_count;

        $response = $this->get("/api/v1/notes/{$this->publishedNote->id}/download");

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));

        $this->publishedNote->refresh();
        $this->assertEquals($initialCount + 1, $this->publishedNote->download_count);
    }

    public function test_preview_and_download_for_draft_note_denied_with_404(): void
    {
        $this->get("/api/v1/notes/{$this->draftNote->id}/preview")
            ->assertStatus(404);

        $this->get("/api/v1/notes/{$this->draftNote->id}/download")
            ->assertStatus(404);
    }

    public function test_thumbnail_delivery_and_missing_thumbnail_handling(): void
    {
        // Valid thumbnail
        $this->get("/api/v1/notes/{$this->publishedNote->id}/thumbnail")
            ->assertOk();

        // Note without thumbnail
        $this->publishedNote->update(['thumbnail_path' => null]);
        $this->get("/api/v1/notes/{$this->publishedNote->id}/thumbnail")
            ->assertStatus(404);
    }
}
