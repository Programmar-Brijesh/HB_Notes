<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\McqQuestionResource;
use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\McqQuestion;
use App\Models\Subject;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class McqQuestionController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of published MCQ questions.
     */
    public function index(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $chapterId = $request->input('chapter_id');
        $difficulty = strtolower(trim((string) $request->input('difficulty', '')));
        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        // Server-side hierarchy consistency check
        if (!empty($chapterId)) {
            $chapter = Chapter::with('subject')->find($chapterId);
            if (!$chapter || !$chapter->is_active) {
                return $this->errorResponse('The specified chapter was not found or is inactive', 404);
            }

            if (!empty($subjectId) && (int) $chapter->subject_id !== (int) $subjectId) {
                return $this->errorResponse('The selected chapter does not belong to the chosen subject', 422);
            }

            if (!empty($classId) && (int) $chapter->subject?->academic_class_id !== (int) $classId) {
                return $this->errorResponse('The selected chapter does not belong to the chosen academic class', 422);
            }
        } elseif (!empty($subjectId)) {
            $subject = Subject::find($subjectId);
            if (!$subject || !$subject->is_active) {
                return $this->errorResponse('The specified subject was not found or is inactive', 404);
            }

            if (!empty($classId) && (int) $subject->academic_class_id !== (int) $classId) {
                return $this->errorResponse('The selected subject does not belong to the chosen academic class', 422);
            }
        } elseif (!empty($classId)) {
            $classExists = AcademicClass::where('is_active', true)->where('id', $classId)->exists();
            if (!$classExists) {
                return $this->errorResponse('The specified academic class was not found or is inactive', 404);
            }
        }

        $query = McqQuestion::where('status', 'published')
            ->where('is_active', true)
            ->whereHas('chapter', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('subject', function ($qs) {
                        $qs->where('is_active', true)
                            ->whereHas('academicClass', function ($qc) {
                                $qc->where('is_active', true);
                            });
                    });
            });

        if (!empty($chapterId)) {
            $query->where('chapter_id', $chapterId);
        } elseif (!empty($subjectId)) {
            $query->whereHas('chapter', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        } elseif (!empty($classId)) {
            $query->whereHas('chapter.subject', function ($q) use ($classId) {
                $q->where('academic_class_id', $classId);
            });
        }

        if (!empty($difficulty) && in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            $query->where('difficulty', $difficulty);
        }

        $questions = $query->orderBy('order_column')
            ->orderBy('id')
            ->paginate($perPage);

        return $this->paginatedResponse(
            $questions,
            McqQuestionResource::collection($questions->items()),
            'Published MCQs fetched successfully'
        );
    }

    /**
     * Display a single published MCQ question.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $question = McqQuestion::where('status', 'published')
            ->where('is_active', true)
            ->whereHas('chapter', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('subject', function ($qs) {
                        $qs->where('is_active', true)
                            ->whereHas('academicClass', function ($qc) {
                                $qc->where('is_active', true);
                            });
                    });
            })
            ->find($id);

        if (!$question) {
            return $this->errorResponse('The requested question was not found or is unavailable', 404);
        }

        return $this->successResponse(
            new McqQuestionResource($question),
            'Question details fetched successfully'
        );
    }

    /**
     * Check a single student answer and reveal the solution.
     */
    public function check(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'selected_option' => ['required', 'string', 'in:A,B,C,D,a,b,c,d'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Invalid submission', 422, $validator->errors());
        }

        $question = McqQuestion::where('status', 'published')
            ->where('is_active', true)
            ->whereHas('chapter', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('subject', function ($qs) {
                        $qs->where('is_active', true)
                            ->whereHas('academicClass', function ($qc) {
                                $qc->where('is_active', true);
                            });
                    });
            })
            ->find($id);

        if (!$question) {
            return $this->errorResponse('The requested question was not found or is unavailable', 404);
        }

        $selected = strtoupper(trim((string) $request->input('selected_option')));
        $isCorrect = ($selected === strtoupper($question->correct_option));

        return $this->successResponse([
            'question_id' => $question->id,
            'selected_option' => $selected,
            'is_correct' => $isCorrect,
            'correct_option' => $question->correct_option,
            'explanation' => $question->explanation,
        ], 'Answer evaluated successfully');
    }

    /**
     * Submit and verify a batch of practice/quiz answers for a chapter.
     */
    public function verify(Request $request, int $chapterId): JsonResponse
    {
        $chapter = Chapter::where('is_active', true)
            ->whereHas('subject', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('academicClass', function ($qc) {
                        $qc->where('is_active', true);
                    });
            })
            ->find($chapterId);

        if (!$chapter) {
            return $this->errorResponse('The specified chapter was not found or is inactive', 404);
        }

        $validator = Validator::make($request->all(), [
            'answers' => ['required', 'array', 'min:1', 'max:100'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.selected_option' => ['required', 'string', 'in:A,B,C,D,a,b,c,d'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Invalid quiz submission payload', 422, $validator->errors());
        }

        $answers = $request->input('answers');
        $questionIds = array_column($answers, 'question_id');

        // Fetch questions belonging to this chapter that are published
        $questions = McqQuestion::where('chapter_id', $chapter->id)
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $results = [];
        $correctCount = 0;
        $totalSubmitted = count($answers);

        foreach ($answers as $item) {
            $qId = (int) $item['question_id'];
            $selected = strtoupper(trim((string) $item['selected_option']));
            $question = $questions->get($qId);

            if (!$question) {
                $results[] = [
                    'question_id' => $qId,
                    'valid' => false,
                    'error' => 'Question does not exist or does not belong to this published chapter',
                ];
                continue;
            }

            $isCorrect = ($selected === strtoupper($question->correct_option));
            if ($isCorrect) {
                $correctCount++;
            }

            $results[] = [
                'question_id' => $qId,
                'valid' => true,
                'selected_option' => $selected,
                'is_correct' => $isCorrect,
                'correct_option' => $question->correct_option,
                'explanation' => $question->explanation,
            ];
        }

        $scorePercentage = $totalSubmitted > 0 ? round(($correctCount / $totalSubmitted) * 100, 2) : 0;

        return $this->successResponse([
            'chapter_id' => $chapter->id,
            'chapter_name' => $chapter->name,
            'total_submitted' => $totalSubmitted,
            'correct_count' => $correctCount,
            'incorrect_count' => $totalSubmitted - $correctCount,
            'score_percentage' => $scorePercentage,
            'results' => $results,
        ], 'Quiz evaluated successfully');
    }
}
