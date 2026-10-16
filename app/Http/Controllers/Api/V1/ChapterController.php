<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChapterResource;
use App\Http\Resources\Api\V1\McqQuestionResource;
use App\Http\Resources\Api\V1\NoteResource;
use App\Models\Chapter;
use App\Models\Subject;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of published chapters, optionally filtered by subject.
     */
    public function index(Request $request): JsonResponse
    {
        $subjectId = $request->input('subject_id');

        $query = Chapter::where('is_active', true)
            ->whereHas('subject', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('academicClass', function ($qc) {
                        $qc->where('is_active', true);
                    });
            })
            ->with(['subject.academicClass'])
            ->withCount([
                'notes' => function ($q) {
                    $q->where('status', 'published')->where('is_active', true);
                },
                'mcqQuestions' => function ($q) {
                    $q->where('status', 'published')->where('is_active', true);
                },
            ]);

        if (!empty($subjectId)) {
            $subjectExists = Subject::where('is_active', true)
                ->whereHas('academicClass', fn ($qc) => $qc->where('is_active', true))
                ->where('id', $subjectId)
                ->exists();

            if (!$subjectExists) {
                return $this->errorResponse('The specified subject was not found or is inactive', 404);
            }

            $query->where('subject_id', $subjectId);
        }

        $chapters = $query->orderBy('chapter_number')
            ->orderBy('order_column')
            ->get();

        return $this->successResponse(
            ChapterResource::collection($chapters),
            'Chapters fetched successfully'
        );
    }

    /**
     * Display the specified chapter.
     */
    public function show(int $id): JsonResponse
    {
        $chapter = Chapter::where('is_active', true)
            ->whereHas('subject', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('academicClass', function ($qc) {
                        $qc->where('is_active', true);
                    });
            })
            ->with(['subject.academicClass'])
            ->withCount([
                'notes' => function ($q) {
                    $q->where('status', 'published')->where('is_active', true);
                },
                'mcqQuestions' => function ($q) {
                    $q->where('status', 'published')->where('is_active', true);
                },
            ])
            ->find($id);

        if (!$chapter) {
            return $this->errorResponse('Chapter not found or is currently inactive', 404);
        }

        return $this->successResponse(
            new ChapterResource($chapter),
            'Chapter details fetched successfully'
        );
    }

    /**
     * Display published notes belonging to the specified chapter.
     */
    public function notes(int $id, Request $request): JsonResponse
    {
        $chapter = Chapter::where('is_active', true)
            ->whereHas('subject', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('academicClass', function ($qc) {
                        $qc->where('is_active', true);
                    });
            })
            ->find($id);

        if (!$chapter) {
            return $this->errorResponse('Chapter not found or is currently inactive', 404);
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        $notesPaginator = $chapter->notes()
            ->where('status', 'published')
            ->where('is_active', true)
            ->orderBy('order_column')
            ->latest('id')
            ->paginate($perPage);

        return $this->paginatedResponse(
            $notesPaginator,
            NoteResource::collection($notesPaginator->items()),
            "Published notes for chapter '{$chapter->name}' fetched successfully"
        );
    }

    /**
     * Display published MCQs belonging to the specified chapter.
     */
    public function mcqs(int $id, Request $request): JsonResponse
    {
        $chapter = Chapter::where('is_active', true)
            ->whereHas('subject', function ($q) {
                $q->where('is_active', true)
                    ->whereHas('academicClass', function ($qc) {
                        $qc->where('is_active', true);
                    });
            })
            ->find($id);

        if (!$chapter) {
            return $this->errorResponse('Chapter not found or is currently inactive', 404);
        }

        $difficulty = strtolower(trim((string) $request->input('difficulty', '')));
        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        $query = $chapter->mcqQuestions()
            ->where('status', 'published')
            ->where('is_active', true);

        if (!empty($difficulty) && in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            $query->where('difficulty', $difficulty);
        }

        $mcqPaginator = $query->orderBy('order_column')
            ->orderBy('id')
            ->paginate($perPage);

        return $this->paginatedResponse(
            $mcqPaginator,
            McqQuestionResource::collection($mcqPaginator->items()),
            "Published MCQs for chapter '{$chapter->name}' fetched successfully"
        );
    }
}
