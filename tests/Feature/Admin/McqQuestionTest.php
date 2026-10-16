<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\McqQuestion;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McqQuestionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private AcademicClass $class10;
    private Subject $math10;
    private Chapter $realNumbers10;

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

        $this->math10 = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Mathematics',
            'slug' => 'mathematics-10',
            'is_active' => true,
        ]);

        $this->realNumbers10 = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 1,
            'name' => 'Real Numbers',
            'slug' => 'real-numbers',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_mcq_index_and_filter(): void
    {
        $q1 = McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Is sqrt(2) rational or irrational?',
            'option_a' => 'Rational',
            'option_b' => 'Irrational',
            'option_c' => 'Integer',
            'option_d' => 'None',
            'correct_option' => 'B',
            'difficulty' => 'easy',
            'status' => 'published',
            'is_active' => true,
        ]);

        $q2 = McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Find the LCM of 24 and 36',
            'option_a' => '72',
            'option_b' => '48',
            'option_c' => '12',
            'option_d' => '96',
            'correct_option' => 'A',
            'difficulty' => 'medium',
            'status' => 'draft',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.index'));
        $response->assertOk();
        $response->assertSee('Is sqrt(2) rational or irrational?');
        $response->assertSee('Find the LCM of 24 and 36');
        $response->assertSee('Import Questions (CSV)');

        // Filter by difficulty
        $resEasy = $this->actingAs($this->admin)->get(route('admin.mcqs.index', ['difficulty' => 'easy']));
        $resEasy->assertSee('Is sqrt(2) rational or irrational?');
        $resEasy->assertDontSee('Find the LCM of 24 and 36');

        // Filter by status: draft
        $resDraft = $this->actingAs($this->admin)->get(route('admin.mcqs.index', ['status' => 'draft']));
        $resDraft->assertSee('Find the LCM of 24 and 36');
        $resDraft->assertDontSee('Is sqrt(2) rational or irrational?');
    }

    public function test_admin_can_create_single_mcq_question(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.mcqs.store'), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'What is the decimal expansion of 7/8?',
            'option_a' => '0.875',
            'option_b' => '0.750',
            'option_c' => '0.825',
            'option_d' => '0.850',
            'correct_option' => 'A',
            'explanation' => '7 divided by 8 is terminating decimal 0.875',
            'difficulty' => 'easy',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.mcqs.index', ['chapter_id' => $this->realNumbers10->id]));
        $this->assertDatabaseHas('mcq_questions', [
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'What is the decimal expansion of 7/8?',
            'correct_option' => 'A',
        ]);
    }

    public function test_admin_can_update_mcq_question(): void
    {
        $q = McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Initial Question Text',
            'option_a' => 'Opt 1',
            'option_b' => 'Opt 2',
            'option_c' => 'Opt 3',
            'option_d' => 'Opt 4',
            'correct_option' => 'A',
            'difficulty' => 'easy',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.mcqs.update', $q), [
            'class_id' => $this->class10->id,
            'subject_id' => $this->math10->id,
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Updated Question Statement',
            'option_a' => 'Opt 1',
            'option_b' => 'Opt 2',
            'option_c' => 'Opt 3',
            'option_d' => 'Opt 4',
            'correct_option' => 'C',
            'difficulty' => 'medium',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.mcqs.index', ['chapter_id' => $this->realNumbers10->id]));
        $this->assertDatabaseHas('mcq_questions', [
            'id' => $q->id,
            'question' => 'Updated Question Statement',
            'correct_option' => 'C',
            'difficulty' => 'medium',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_delete_mcq_question(): void
    {
        $q = McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Question to delete',
            'option_a' => '1',
            'option_b' => '2',
            'option_c' => '3',
            'option_d' => '4',
            'correct_option' => 'B',
            'difficulty' => 'easy',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.mcqs.destroy', $q));
        $response->assertRedirect(route('admin.mcqs.index', ['chapter_id' => $this->realNumbers10->id]));
        $this->assertDatabaseMissing('mcq_questions', ['id' => $q->id]);
    }

    public function test_cannot_delete_chapter_with_dependent_mcqs(): void
    {
        McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Blocking Question',
            'option_a' => '1',
            'option_b' => '2',
            'option_c' => '3',
            'option_d' => '4',
            'correct_option' => 'A',
            'difficulty' => 'easy',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.chapters.destroy', $this->realNumbers10));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('chapters', ['id' => $this->realNumbers10->id]);
    }

    public function test_question_bank_navigation_and_action_links(): void
    {
        $q = McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Navigation Test Question',
            'option_a' => '1',
            'option_b' => '2',
            'option_c' => '3',
            'option_d' => '4',
            'correct_option' => 'A',
            'difficulty' => 'easy',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.index'));

        $response->assertOk();
        $response->assertSee(route('admin.mcqs.create'));
        $response->assertSee(route('admin.mcqs.import'));
        $response->assertSee(route('admin.mcqs.import.history'));
        $response->assertSee(route('admin.mcqs.import.template'));
        $response->assertSee(route('admin.mcqs.edit', $q));
    }

    public function test_pagination_preserves_query_string_filters(): void
    {
        // Create 20 questions to trigger pagination (15 per page)
        for ($i = 1; $i <= 20; $i++) {
            McqQuestion::create([
                'chapter_id' => $this->realNumbers10->id,
                'question' => "Numbered Test Question {$i}",
                'option_a' => 'A',
                'option_b' => 'B',
                'option_c' => 'C',
                'option_d' => 'D',
                'correct_option' => 'A',
                'difficulty' => 'easy',
                'status' => 'published',
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.mcqs.index', [
            'search' => 'Numbered',
            'difficulty' => 'easy',
            'status' => 'published',
        ]));

        $response->assertOk();
        $response->assertSee('Numbered Test Question');
        $response->assertSee('page=2');
        $response->assertSee('search=Numbered');
        $response->assertSee('difficulty=easy');
        $response->assertSee('status=published');
    }

    public function test_create_and_edit_pages_render_successfully(): void
    {
        $q = McqQuestion::create([
            'chapter_id' => $this->realNumbers10->id,
            'question' => 'Sample Question For Edit',
            'option_a' => 'Opt A',
            'option_b' => 'Opt B',
            'option_c' => 'Opt C',
            'option_d' => 'Opt D',
            'correct_option' => 'B',
            'difficulty' => 'medium',
            'status' => 'published',
        ]);

        $createRes = $this->actingAs($this->admin)->get(route('admin.mcqs.create'));
        $createRes->assertOk();
        $createRes->assertSee('Create Single MCQ Question');
        $createRes->assertSee(route('admin.mcqs.index'));
        $createRes->assertSee(route('admin.mcqs.import'));

        $editRes = $this->actingAs($this->admin)->get(route('admin.mcqs.edit', $q));
        $editRes->assertOk();
        $editRes->assertSee('Edit MCQ Question');
        $editRes->assertSee('Sample Question For Edit');
        $editRes->assertSee(route('admin.mcqs.index'));
    }
}

