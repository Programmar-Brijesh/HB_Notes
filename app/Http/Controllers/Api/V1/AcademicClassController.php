<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AcademicClassResource;
use App\Http\Resources\Api\V1\SubjectResource;
use App\Models\AcademicClass;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicClassController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all published academic classes.
     */
    public function index(Request $request): JsonResponse
    {
        $classes = AcademicClass::where('is_active', true)
            ->withCount(['subjects' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('class_number')
            ->get();

        return $this->successResponse(
            AcademicClassResource::collection($classes),
            'Academic classes fetched successfully'
        );
    }

    /**
     * Display the specified academic class.
     */
    public function show(int $id): JsonResponse
    {
        $class = AcademicClass::where('is_active', true)
            ->withCount(['subjects' => function ($q) {
                $q->where('is_active', true);
            }])
            ->find($id);

        if (!$class) {
            return $this->errorResponse('Academic class not found or is currently inactive', 404);
        }

        return $this->successResponse(
            new AcademicClassResource($class),
            'Academic class details fetched successfully'
        );
    }

    /**
     * Display subjects belonging to the specified academic class.
     */
    public function subjects(int $id): JsonResponse
    {
        $class = AcademicClass::where('is_active', true)->find($id);

        if (!$class) {
            return $this->errorResponse('Academic class not found or is currently inactive', 404);
        }

        $subjects = $class->subjects()
            ->where('is_active', true)
            ->withCount(['chapters' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('order_column')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            SubjectResource::collection($subjects),
            "Subjects for {$class->name} fetched successfully"
        );
    }
}
