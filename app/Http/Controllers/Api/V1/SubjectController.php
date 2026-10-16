<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChapterResource;
use App\Http\Resources\Api\V1\SubjectResource;
use App\Models\AcademicClass;
use App\Models\Subject;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of published subjects, optionally filtered by academic class.
     */
    public function index(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');

        $query = Subject::where('is_active', true)
            ->whereHas('academicClass', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['academicClass'])
            ->withCount(['chapters' => function ($q) {
                $q->where('is_active', true);
            }]);

        if (!empty($classId)) {
            $classExists = AcademicClass::where('is_active', true)->where('id', $classId)->exists();
            if (!$classExists) {
                return $this->errorResponse('The specified academic class was not found or is inactive', 404);
            }
            $query->where('academic_class_id', $classId);
        }

        $subjects = $query->orderBy('order_column')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            SubjectResource::collection($subjects),
            'Subjects fetched successfully'
        );
    }

    /**
     * Display the specified subject.
     */
    public function show(int $id): JsonResponse
    {
        $subject = Subject::where('is_active', true)
            ->whereHas('academicClass', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['academicClass'])
            ->withCount(['chapters' => function ($q) {
                $q->where('is_active', true);
            }])
            ->find($id);

        if (!$subject) {
            return $this->errorResponse('Subject not found or is currently inactive', 404);
        }

        return $this->successResponse(
            new SubjectResource($subject),
            'Subject details fetched successfully'
        );
    }

    /**
     * Display chapters belonging to the specified subject.
     */
    public function chapters(int $id): JsonResponse
    {
        $subject = Subject::where('is_active', true)
            ->whereHas('academicClass', function ($q) {
                $q->where('is_active', true);
            })
            ->find($id);

        if (!$subject) {
            return $this->errorResponse('Subject not found or is currently inactive', 404);
        }

        $chapters = $subject->chapters()
            ->where('is_active', true)
            ->withCount([
                'notes' => function ($q) {
                    $q->where('status', 'published')->where('is_active', true);
                },
                'mcqQuestions' => function ($q) {
                    $q->where('status', 'published')->where('is_active', true);
                },
            ])
            ->orderBy('chapter_number')
            ->orderBy('order_column')
            ->get();

        return $this->successResponse(
            ChapterResource::collection($chapters),
            "Chapters for {$subject->name} fetched successfully"
        );
    }
}
