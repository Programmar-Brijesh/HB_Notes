<?php

namespace Tests\Feature\Api\V1;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\McqQuestion;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McqApiTest extends TestCase
{
    use RefreshDatabase;

    private AcademicClass $class10;
    private Subject $math10;
    private Chapter $realNumbers;
    private Chapter $otherChapter;
    private McqQuestion $publishedQ1;
    private McqQuestion $publishedQ2;
    private McqQuestion $draftQuestion;
    private McqQuestion $inactiveQuestion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        $this->class10 = AcademicClass::where('class_number', 10)->firstOrFail();

        $this->math10 = Subject::create([
            'academic_class_id' => $this->class10->id,
            'name' => 'Mathematics',
            'slug' => 'mathematics-10',
            'is_active' => true,
        ]);

        $this->realNumbers = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 1,
            'name' => 'Real Numbers',
            'slug' => 'real-numbers',
            'is_active' => true,
        ]);

        $this->otherChapter = Chapter::create([
            'subject_id' => $this->math10->id,
            'chapter_number' => 2,
            'name' => 'Polynomials',
            'slug' => 'polynomials',
            'is_active' => true,
        ]);

        $this->publishedQ1 = McqQuestion::create([
            'chapter_id' => $this->realNumbers->id,
            'question' => 'What type of number is √2?',
            'option_a' => 'Rational',
            'option_b' => 'Irrational',
            'option_c' => 'Integer',
            'option_d' => 'Whole number',
            'correct_option' => 'B',
            'explanation' => '√2 cannot be written as p/q where p and q are coprime integers.',
            'difficulty' => 'easy',
            'status' => 'published',
            'is_active' => true,
            'order_column' => 1,
        ]);

        $this->publishedQ2 = McqQuestion::create([
            'chapter_id' => $this->realNumbers->id,
            'question' => 'The HCF of 96 and 404 is:',
            'option_a' => '4',
            'option_b' => '2',
            'option_c' => '8',
            'option_d' => '12',
            'correct_option' => 'A',
            'explanation' => 'Prime factorisation gives 96 = 2^5 * 3 and 404 = 2^2 * 101. HCF = 2^2 = 4.',
            'difficulty' => 'medium',
            'status' => 'published',
            'is_active' => true,
            'order_column' => 2,
        ]);

        $this->draftQuestion = McqQuestion::create([
            'chapter_id' => $this->realNumbers->id,
            'question' => 'Draft question for future review',
            'option_a' => 'Opt A',
            'option_b' => 'Opt B',
            'option_c' => 'Opt C',
            'option_d' => 'Opt D',
            'correct_option' => 'C',
            'explanation' => 'Draft explanation',
            'difficulty' => 'hard',
            'status' => 'draft',
            'is_active' => true,
            'order_column' => 3,
        ]);

        $this->inactiveQuestion = McqQuestion::create([
            'chapter_id' => $this->realNumbers->id,
            'question' => 'Inactive deprecated question',
            'option_a' => 'Opt A',
            'option_b' => 'Opt B',
            'option_c' => 'Opt C',
            'option_d' => 'Opt D',
            'correct_option' => 'D',
            'explanation' => 'Inactive explanation',
            'difficulty' => 'medium',
            'status' => 'published',
            'is_active' => false,
            'order_column' => 4,
        ]);
    }

    public function test_can_fetch_published_mcqs_and_answers_are_hidden_in_practice_mode(): void
    {
        $response = $this->getJson("/api/v1/chapters/{$this->realNumbers->id}/mcqs");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'chapter_id',
                        'question',
                        'option_a',
                        'option_b',
                        'option_c',
                        'option_d',
                        'difficulty',
                        'order_column',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $items = $response->json('data');
        $this->assertCount(2, $items);

        // Crucial security requirement: answers and explanations must NOT be exposed in practice mode
        foreach ($items as $item) {
            $this->assertArrayNotHasKey('correct_option', $item);
            $this->assertArrayNotHasKey('explanation', $item);
        }

        // Draft and inactive questions are excluded
        $ids = collect($items)->pluck('id')->all();
        $this->assertContains($this->publishedQ1->id, $ids);
        $this->assertContains($this->publishedQ2->id, $ids);
        $this->assertNotContains($this->draftQuestion->id, $ids);
        $this->assertNotContains($this->inactiveQuestion->id, $ids);
    }

    public function test_study_mode_reveals_correct_option_and_explanation(): void
    {
        $response = $this->getJson("/api/v1/chapters/{$this->realNumbers->id}/mcqs?mode=study");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $items = $response->json('data');
        $this->assertCount(2, $items);

        $first = collect($items)->firstWhere('id', $this->publishedQ1->id);
        $this->assertNotNull($first);
        $this->assertEquals('B', $first['correct_option']);
        $this->assertStringContainsString('coprime', $first['explanation']);
    }

    public function test_mcq_difficulty_filter(): void
    {
        $response = $this->getJson("/api/v1/chapters/{$this->realNumbers->id}/mcqs?difficulty=easy");

        $response->assertOk();
        $items = $response->json('data');
        $this->assertCount(1, $items);
        $this->assertEquals($this->publishedQ1->id, $items[0]['id']);
    }

    public function test_single_mcq_check_endpoint(): void
    {
        // Correct submission
        $correctResponse = $this->postJson("/api/v1/mcqs/{$this->publishedQ1->id}/check", [
            'selected_option' => 'B',
        ]);

        $correctResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.question_id', $this->publishedQ1->id)
            ->assertJsonPath('data.selected_option', 'B')
            ->assertJsonPath('data.is_correct', true)
            ->assertJsonPath('data.correct_option', 'B')
            ->assertJsonPath('data.explanation', $this->publishedQ1->explanation);

        // Incorrect submission
        $incorrectResponse = $this->postJson("/api/v1/mcqs/{$this->publishedQ1->id}/check", [
            'selected_option' => 'A',
        ]);

        $incorrectResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_correct', false)
            ->assertJsonPath('data.correct_option', 'B');
    }

    public function test_single_mcq_check_rejects_invalid_option(): void
    {
        $response = $this->postJson("/api/v1/mcqs/{$this->publishedQ1->id}/check", [
            'selected_option' => 'Z',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_chapter_quiz_verify_batch_evaluation(): void
    {
        $payload = [
            'answers' => [
                [
                    'question_id' => $this->publishedQ1->id,
                    'selected_option' => 'B', // Correct
                ],
                [
                    'question_id' => $this->publishedQ2->id,
                    'selected_option' => 'C', // Incorrect (Correct is A)
                ],
            ],
        ];

        $response = $this->postJson("/api/v1/chapters/{$this->realNumbers->id}/mcqs/verify", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_submitted', 2)
            ->assertJsonPath('data.correct_count', 1)
            ->assertJsonPath('data.incorrect_count', 1)
            ->assertJsonPath('data.score_percentage', 50);

        $results = $response->json('data.results');
        $this->assertCount(2, $results);

        $q1Result = collect($results)->firstWhere('question_id', $this->publishedQ1->id);
        $this->assertTrue($q1Result['is_correct']);
        $this->assertEquals('B', $q1Result['correct_option']);

        $q2Result = collect($results)->firstWhere('question_id', $this->publishedQ2->id);
        $this->assertFalse($q2Result['is_correct']);
        $this->assertEquals('A', $q2Result['correct_option']);
        $this->assertNotNull($q2Result['explanation']);
    }

    public function test_chapter_quiz_verify_rejects_empty_answers(): void
    {
        $response = $this->postJson("/api/v1/chapters/{$this->realNumbers->id}/mcqs/verify", [
            'answers' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_chapter_quiz_verify_rejects_inactive_chapter(): void
    {
        $this->realNumbers->update(['is_active' => false]);

        $response = $this->postJson("/api/v1/chapters/{$this->realNumbers->id}/mcqs/verify", [
            'answers' => [
                ['question_id' => $this->publishedQ1->id, 'selected_option' => 'B'],
            ],
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
