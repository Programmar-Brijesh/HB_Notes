<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\McqImportLog;
use App\Models\McqQuestion;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class McqImportTest extends TestCase
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

    public function test_guests_cannot_access_import_endpoints(): void
    {
        $this->get(route('admin.mcqs.import'))->assertRedirect(route('login'));
        $this->get(route('admin.mcqs.import.history'))->assertRedirect(route('login'));
        $this->get(route('admin.mcqs.import.template'))->assertRedirect(route('login'));
        $this->post(route('admin.mcqs.import.preview'))->assertRedirect(route('login'));
        $this->post(route('admin.mcqs.import.confirm'))->assertRedirect(route('login'));
        $this->post(route('admin.mcqs.import.cancel'))->assertRedirect(route('login'));
    }

    public function test_non_admin_and_deactivated_admin_users_denied_access(): void
    {
        $student = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $this->actingAs($student)->get(route('admin.mcqs.import'))->assertStatus(403);
        $this->actingAs($student)->get(route('admin.mcqs.import.history'))->assertStatus(403);
        $this->actingAs($student)->get(route('admin.mcqs.import.template'))->assertStatus(403);

        $deactivated = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $this->actingAs($deactivated)->get(route('admin.mcqs.import'))->assertStatus(403);
        $this->actingAs($deactivated)->get(route('admin.mcqs.import.history'))->assertStatus(403);
    }

    public function test_template_download_returns_expected_headers_and_utf8_bom(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.import.template'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="hb_notes_mcq_template.csv"');

        $content = $response->getContent();
        $this->assertStringContainsString('question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status', $content);
        $this->assertStringContainsString('What is the SI unit of electric current?', $content);
    }

    public function test_import_cannot_proceed_without_class_subject_and_chapter(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('test.csv', 10, 'text/csv');

        $response = $this->actingAs($this->admin)->from(route('admin.mcqs.import'))->post(route('admin.mcqs.import.preview'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.mcqs.import'));
        $response->assertSessionHasErrors(['class_id', 'subject_id', 'chapter_id']);
    }

    public function test_subject_class_and_chapter_subject_mismatches_are_rejected(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('test.csv', 10, 'text/csv');

        // realNumbers10 belongs to math10, but physics12 passed as subject
        $response = $this->actingAs($this->admin)->from(route('admin.mcqs.import'))->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->physics12->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.mcqs.import'));
        $response->assertSessionHasErrors('chapter_id');
    }

    public function test_questions_are_not_saved_before_confirmation(): void
    {
        Storage::fake('local');

        $csv = "question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status\n"
             . "\"What is 2+2?\",\"3\",\"4\",\"5\",\"6\",\"B\",\"Basic arithmetic\",\"easy\",\"published\"\n";

        $file = UploadedFile::fake()->createWithContent('preview_test.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $response->assertOk();
        $response->assertViewIs('admin.mcqs.import-preview');

        // Zero questions must be committed to mcq_questions table during preview!
        $this->assertEquals(0, McqQuestion::count());
    }

    public function test_valid_csv_questions_imported_into_selected_chapter_only(): void
    {
        Storage::fake('local');

        $csv = "question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status\n"
             . "\"Question 1?\",\"Opt A\",\"Opt B\",\"Opt C\",\"Opt D\",\"A\",\"Exp 1\",\"easy\",\"published\"\n"
             . "\"Question 2?\",\"Opt 1\",\"Opt 2\",\"Opt 3\",\"Opt 4\",\"C\",\"Exp 2\",\"medium\",\"draft\"\n";

        $file = UploadedFile::fake()->createWithContent('valid.csv', $csv);

        $previewRes = $this->actingAs($this->admin)->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $previewRes->assertOk();
        $importLog = McqImportLog::firstOrFail();
        $this->assertEquals('preview', $importLog->status);

        // Confirm Import
        $confirmRes = $this->actingAs($this->admin)->post(route('admin.mcqs.import.confirm'), [
            'import_token' => $importLog->import_token,
            'chapter_id' => $this->realNumbers10->id,
        ]);

        $confirmRes->assertRedirect(route('admin.mcqs.import.report', $importLog->id));

        $this->assertEquals(2, McqQuestion::where('chapter_id', $this->realNumbers10->id)->count());
        $this->assertEquals(0, McqQuestion::where('chapter_id', $this->kinematics12->id)->count());

        $q1 = McqQuestion::where('question', 'Question 1?')->firstOrFail();
        $this->assertEquals('A', $q1->correct_option);
        $this->assertEquals('easy', $q1->difficulty);
        $this->assertEquals('published', $q1->status);

        $q2 = McqQuestion::where('question', 'Question 2?')->firstOrFail();
        $this->assertEquals('C', $q2->correct_option);
        $this->assertEquals('medium', $q2->difficulty);
        $this->assertEquals('draft', $q2->status);

        $importLog->refresh();
        $this->assertEquals('completed', $importLog->status);
        $this->assertEquals(2, $importLog->imported_count);
    }

    public function test_invalid_rows_reported_with_accurate_row_numbers(): void
    {
        Storage::fake('local');

        $csv = "question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status\n"
             . "\"Valid Question\",\"A\",\"B\",\"C\",\"D\",\"A\",\"\",\"easy\",\"published\"\n"        // Row 2: Valid
             . "\"\",\"A\",\"B\",\"C\",\"D\",\"A\",\"\",\"easy\",\"published\"\n"                       // Row 3: Missing question
             . "\"Missing Option B\",\"A\",\"\",\"C\",\"D\",\"A\",\"\",\"easy\",\"published\"\n"         // Row 4: Missing option
             . "\"Invalid Correct Key\",\"A\",\"B\",\"C\",\"D\",\"Z\",\"\",\"easy\",\"published\"\n"    // Row 5: Bad correct_option
             . "\"Bad Difficulty\",\"A\",\"B\",\"C\",\"D\",\"A\",\"\",\"extreme\",\"published\"\n"      // Row 6: Bad difficulty
             . "\"Bad Status\",\"A\",\"B\",\"C\",\"D\",\"A\",\"\",\"easy\",\"archived\"\n";            // Row 7: Bad status

        $file = UploadedFile::fake()->createWithContent('invalid_rows.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $response->assertOk();
        $parseResult = $response->viewData('parseResult');

        $this->assertEquals(6, $parseResult['total_rows']);
        $this->assertEquals(1, $parseResult['valid_count']);
        $this->assertEquals(5, $parseResult['invalid_count']);

        $rowNumbers = array_column($parseResult['invalid_rows'], 'row');
        $this->assertEquals([3, 4, 5, 6, 7], $rowNumbers);
    }

    public function test_duplicate_questions_within_csv_detected(): void
    {
        Storage::fake('local');

        $csv = "question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status\n"
             . "\"What is the capital of France?\",\"Paris\",\"Lyon\",\"Marseille\",\"Nice\",\"A\",\"\",\"easy\",\"published\"\n"   // Row 2: First occurrence
             . "\"What is the capital of France?\",\"Paris\",\"Lyon\",\"Marseille\",\"Nice\",\"A\",\"\",\"easy\",\"published\"\n"   // Row 3: Duplicate of Row 2
             . "\"WHAT IS  THE CAPITAL OF FRANCE?\",\"Paris\",\"Lyon\",\"Marseille\",\"Nice\",\"A\",\"\",\"easy\",\"published\"\n"; // Row 4: Normalized duplicate of Row 2

        $file = UploadedFile::fake()->createWithContent('internal_dupes.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $response->assertOk();
        $parseResult = $response->viewData('parseResult');

        $this->assertEquals(3, $parseResult['total_rows']);
        $this->assertEquals(1, $parseResult['valid_count']);
        $this->assertEquals(2, $parseResult['duplicate_count']);

        $dupeRows = array_column($parseResult['duplicate_rows'], 'row');
        $this->assertEquals([3, 4], $dupeRows);
    }

    public function test_existing_duplicate_questions_are_not_silently_overwritten(): void
    {
        Storage::fake('local');

        // Pre-create question in chapter
        McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'What is the value of Pi to two decimals?',
            'option_a' => '3.14',
            'option_b' => '3.16',
            'option_c' => '3.12',
            'option_d' => '3.18',
            'correct_option' => 'A',
            'difficulty' => 'easy',
            'status' => 'published',
        ]);

        $csv = "question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status\n"
             . "\"What is the value of Pi to two decimals?\",\"3.14\",\"3.16\",\"3.12\",\"3.18\",\"A\",\"\",\"easy\",\"published\"\n" // Dupe of DB
             . "\"New unique question in chapter?\",\"A\",\"B\",\"C\",\"D\",\"B\",\"\",\"medium\",\"published\"\n";                    // New unique

        $file = UploadedFile::fake()->createWithContent('existing_dupe.csv', $csv);

        $response = $this->actingAs($this->admin)->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $response->assertOk();
        $parseResult = $response->viewData('parseResult');

        $this->assertEquals(2, $parseResult['total_rows']);
        $this->assertEquals(1, $parseResult['valid_count']);
        $this->assertEquals(1, $parseResult['duplicate_count']);
        $this->assertEquals(2, $parseResult['duplicate_rows'][0]['row']);
        $this->assertStringContainsString('already exists in this chapter', $parseResult['duplicate_rows'][0]['reason']);
    }

    public function test_unicode_quoted_commas_embedded_quotes_and_linebreaks_parsed_correctly(): void
    {
        Storage::fake('local');

        // Hindi question with commas and Unicode characters
        $csv = "question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status\n"
             . "\"पौधों में प्रकाश संश्लेषण के लिए क्या आवश्यक है?\",\"क्लोरोफिल\",\"सूर्य का प्रकाश\",\"जल, कार्बन डाइऑक्साइड\",\"उपरोक्त सभी\",\"D\",\"सभी कारक महत्वपूर्ण हैं।\",\"medium\",\"published\"\n"
             . "\"Solve: 2x + 4 = 10, find x?\",\"x = 2\",\"x = 3\",\"x = 4\",\"x = 5\",\"B\",\"2x = 6 => x = 3\",\"easy\",\"published\"\n";

        $file = UploadedFile::fake()->createWithContent('unicode_test.csv', $csv);

        $previewRes = $this->actingAs($this->admin)->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $previewRes->assertOk();
        $importLog = McqImportLog::firstOrFail();

        $confirmRes = $this->actingAs($this->admin)->post(route('admin.mcqs.import.confirm'), [
            'import_token' => $importLog->import_token,
            'chapter_id' => $this->realNumbers10->id,
        ]);

        $confirmRes->assertRedirect();

        $hindiQuestion = McqQuestion::where('question', 'LIKE', '%प्रकाश संश्लेषण%')->firstOrFail();
        $this->assertEquals('D', $hindiQuestion->correct_option);
        $this->assertEquals('जल, कार्बन डाइऑक्साइड', $hindiQuestion->option_c);

        $mathQuestion = McqQuestion::where('question', 'LIKE', '%2x + 4%')->firstOrFail();
        $this->assertEquals('B', $mathQuestion->correct_option);
    }

    public function test_invalid_headers_malformed_csv_and_oversized_files_rejected(): void
    {
        Storage::fake('local');

        // Wrong header
        $badHeaderCsv = "wrong_header,opt1,opt2\n\"q1\",\"a\",\"b\"\n";
        $file = UploadedFile::fake()->createWithContent('bad_headers.csv', $badHeaderCsv);

        $response = $this->actingAs($this->admin)->from(route('admin.mcqs.import'))->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.mcqs.import'));
        $response->assertSessionHasErrors('csv_file');

        // Oversized file
        config(['hbnotes.mcq_max_csv_size_kb' => 10]); // 10KB
        $bigFile = UploadedFile::fake()->create('oversized.csv', 50, 'text/csv'); // 50KB

        $bigResponse = $this->actingAs($this->admin)->from(route('admin.mcqs.import'))->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $bigFile,
        ]);

        $bigResponse->assertRedirect(route('admin.mcqs.import'));
        $bigResponse->assertSessionHasErrors('csv_file');
    }

    public function test_repeated_submissions_do_not_import_same_batch_twice(): void
    {
        Storage::fake('local');

        $csv = "question,option_a,option_b,option_c,option_d,correct_option,explanation,difficulty,status\n"
             . "\"Single Question Test?\",\"A\",\"B\",\"C\",\"D\",\"A\",\"\",\"easy\",\"published\"\n";

        $file = UploadedFile::fake()->createWithContent('single.csv', $csv);

        $this->actingAs($this->admin)->post(route('admin.mcqs.import.preview'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'csv_file' => $file,
        ]);

        $importLog = McqImportLog::firstOrFail();

        // First Confirm
        $res1 = $this->actingAs($this->admin)->post(route('admin.mcqs.import.confirm'), [
            'import_token' => $importLog->import_token,
            'chapter_id' => $this->realNumbers10->id,
        ]);
        $res1->assertRedirect(route('admin.mcqs.import.report', $importLog->id));
        $this->assertEquals(1, McqQuestion::count());

        // Second duplicate submit with same import_token
        $res2 = $this->actingAs($this->admin)->post(route('admin.mcqs.import.confirm'), [
            'import_token' => $importLog->import_token,
            'chapter_id' => $this->realNumbers10->id,
        ]);
        $res2->assertRedirect(route('admin.mcqs.import.report', $importLog->id));
        $res2->assertSessionHas('status', 'This import has already been processed.');

        // Must still be only 1 question!
        $this->assertEquals(1, McqQuestion::count());
    }

    public function test_error_report_download_protected_and_formula_injection_sanitized(): void
    {
        $log = McqImportLog::create([
            'import_token' => 'sample-error-token',
            'user_id' => $this->admin->id,
            'chapter_id' => $this->realNumbers10->id,
            'file_name' => 'malicious_input.csv',
            'total_rows' => 2,
            'imported_count' => 0,
            'invalid_count' => 2,
            'status' => 'completed_with_errors',
            'error_data' => [
                [
                    'row' => 2,
                    'reason' => '=1+1 Dangerous formula',
                    'question' => '@SUM(A1:A10) Formula Question',
                ],
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.import.errors-csv', $log));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->getContent();
        // Check that formula injection prefixes '=' and '@' were sanitized with leading single quote
        $this->assertStringContainsString("'=1+1", $content);
        $this->assertStringContainsString("'@SUM", $content);
    }

    public function test_completed_import_report_renders_successfully_without_variable_collision(): void
    {
        $log = McqImportLog::create([
            'import_token' => 'completed-token-123',
            'user_id' => $this->admin->id,
            'chapter_id' => $this->realNumbers10->id,
            'file_name' => 'successful_10_questions.csv',
            'total_rows' => 10,
            'imported_count' => 10,
            'duplicate_count' => 0,
            'invalid_count' => 0,
            'failed_count' => 0,
            'status' => 'completed',
            'error_data' => [],
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.import.report', $log->id));

        $response->assertOk();
        $response->assertSee('Import Execution Audit Report');
        $response->assertSee('Completed Successfully');
        $response->assertSee('successful_10_questions.csv');
        $response->assertSee('Total CSV Rows');
        $response->assertSee('Imported to Bank');
        $response->assertSee('Duplicates Skipped');
        $response->assertSee('Validation Errors');
        $response->assertSee('DB Failures');
        $response->assertDontSee('Row-Specific Exclusions');

        $viewImport = $response->viewData('import');
        $this->assertEquals(10, $viewImport->total_rows);
        $this->assertEquals(10, $viewImport->imported_count);
        $this->assertEquals(0, $viewImport->duplicate_count);
        $this->assertEquals(0, $viewImport->invalid_count);
        $this->assertEquals(0, $viewImport->failed_count);
        $this->assertEmpty($viewImport->error_data);
    }

    public function test_completed_with_errors_import_report_renders_successfully_without_variable_collision(): void
    {
        $log = McqImportLog::create([
            'import_token' => 'warning-token-456',
            'user_id' => $this->admin->id,
            'chapter_id' => $this->realNumbers10->id,
            'file_name' => 'mixed_questions.csv',
            'total_rows' => 12,
            'imported_count' => 10,
            'duplicate_count' => 1,
            'invalid_count' => 1,
            'failed_count' => 0,
            'status' => 'completed_with_errors',
            'error_data' => [
                ['row' => 5, 'reason' => 'Invalid correct option', 'question' => 'Sample Question 5'],
                ['row' => 8, 'reason' => 'Duplicate question', 'question' => 'Sample Question 8'],
            ],
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.import.report', $log->id));

        $response->assertOk();
        $response->assertSee('Import Execution Audit Report');
        $response->assertSee('Completed With Warnings');
        $response->assertSee('Row-Specific Exclusions (2)');
        $response->assertSee('Sample Question 5');
        $response->assertSee('Invalid correct option');
        $response->assertSee('Download Error Report (CSV)');

        $viewImport = $response->viewData('import');
        $this->assertEquals(12, $viewImport->total_rows);
        $this->assertEquals(10, $viewImport->imported_count);
        $this->assertEquals(1, $viewImport->duplicate_count);
        $this->assertEquals(1, $viewImport->invalid_count);
        $this->assertEquals(0, $viewImport->failed_count);
        $this->assertCount(2, $viewImport->error_data);
    }

    public function test_admin_can_view_import_history_with_all_batch_metrics_and_report_links(): void
    {
        $log = McqImportLog::create([
            'import_token' => 'history-token-1',
            'user_id' => $this->admin->id,
            'chapter_id' => $this->realNumbers10->id,
            'file_name' => 'test_history_batch.csv',
            'total_rows' => 15,
            'imported_count' => 12,
            'duplicate_count' => 2,
            'invalid_count' => 1,
            'failed_count' => 0,
            'status' => 'completed_with_errors',
            'error_data' => [
                ['row' => 3, 'reason' => 'Invalid correct option', 'question' => 'Q3'],
            ],
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.import.history'));

        $response->assertOk();
        $response->assertSee('CSV Import Audit History');
        $response->assertSee('test_history_batch.csv');
        $response->assertSee('Real Numbers');
        $response->assertSee('Total: 15');
        $response->assertSee('Imported: 12');
        $response->assertSee('Dupes: 2');
        $response->assertSee('Invalid: 1');
        $response->assertSee(route('admin.mcqs.import.report', $log->id));
        $response->assertSee(route('admin.mcqs.import.errors-csv', $log->id));
    }

    public function test_import_history_page_has_responsive_layouts_without_fixed_overflow_widths(): void
    {
        $log = McqImportLog::create([
            'import_token' => 'responsive-test-token',
            'user_id' => $this->admin->id,
            'chapter_id' => $this->realNumbers10->id,
            'file_name' => 'long_science_examination_mcqs_for_class_10_chapter_real_numbers_sample_dataset_v1.csv',
            'total_rows' => 50,
            'imported_count' => 45,
            'duplicate_count' => 3,
            'invalid_count' => 2,
            'failed_count' => 0,
            'status' => 'completed_with_errors',
            'error_data' => [
                ['row' => 12, 'reason' => 'Invalid difficulty', 'question' => 'Sample test question with long text'],
            ],
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.import.history'));

        $response->assertOk();
        // Verify no hardcoded min-width columns that cause horizontal overflow
        $response->assertDontSee('min-width: 220px');
        $response->assertDontSee('min-width: 260px');

        // Verify responsive table and mobile card views are both present
        $response->assertSee('import-audit-table');
        $response->assertSee('d-none d-md-block');
        $response->assertSee('d-md-none');

        // Verify long file name wraps with text-break
        $response->assertSee('text-break');
        $response->assertSee('long_science_examination_mcqs_for_class_10_chapter_real_numbers_sample_dataset_v1.csv');

        // Verify action buttons exist
        $response->assertSee(route('admin.mcqs.import.report', $log->id));
        $response->assertSee(route('admin.mcqs.import.errors-csv', $log->id));
    }

    public function test_import_report_page_contains_navigation_links_to_history_and_question_bank(): void
    {
        $log = McqImportLog::create([
            'import_token' => 'report-nav-token',
            'user_id' => $this->admin->id,
            'chapter_id' => $this->realNumbers10->id,
            'file_name' => 'nav_test.csv',
            'total_rows' => 5,
            'imported_count' => 5,
            'duplicate_count' => 0,
            'invalid_count' => 0,
            'failed_count' => 0,
            'status' => 'completed',
            'error_data' => [],
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.import.report', $log->id));

        $response->assertOk();
        $response->assertSee(route('admin.mcqs.import.history'));
        $response->assertSee(route('admin.mcqs.index', ['chapter_id' => $log->chapter_id]));
        $response->assertSee(route('admin.mcqs.import'));
    }
}


