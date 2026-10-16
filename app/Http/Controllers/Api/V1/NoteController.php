<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NoteDetailResource;
use App\Http\Resources\Api\V1\NoteResource;
use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Note;
use App\Models\Subject;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NoteController extends Controller
{
    use ApiResponse;

    /**
     * Display a paginated listing of published educational notes.
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $chapterId = $request->input('chapter_id');
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

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

        // Base query: published and active, under active hierarchy
        $query = Note::where('status', 'published')
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

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

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

        $notes = $query->orderBy('order_column')
            ->latest('id')
            ->paginate($perPage);

        return $this->paginatedResponse(
            $notes,
            NoteResource::collection($notes->items()),
            'Published notes fetched successfully'
        );
    }

    /**
     * Display detailed metadata for a published note.
     */
    public function show(int $id): JsonResponse
    {
        $note = Note::where('status', 'published')
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
            ->with(['chapter.subject.academicClass'])
            ->find($id);

        if (!$note) {
            return $this->errorResponse('The requested note was not found or is unavailable', 404);
        }

        return $this->successResponse(
            new NoteDetailResource($note),
            'Note details fetched successfully'
        );
    }

    /**
     * Securely preview the note PDF inline.
     */
    public function preview(int $id)
    {
        $note = $this->findPublishedNote($id);
        if (!$note) {
            return $this->errorResponse('The requested note file was not found or is unpublished', 404);
        }

        $disk = config('hbnotes.pdf_storage_disk', 'local');
        if (!Storage::disk($disk)->exists($note->pdf_path)) {
            return $this->errorResponse('The document file does not exist in storage', 404);
        }

        $fullPath = Storage::disk($disk)->path($note->pdf_path);
        $filename = $note->file_original_name ?: ($note->slug . '.pdf');

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Securely download the note PDF as an attachment and increment download metrics.
     */
    public function download(int $id)
    {
        $note = $this->findPublishedNote($id);
        if (!$note) {
            return $this->errorResponse('The requested note file was not found or is unpublished', 404);
        }

        $disk = config('hbnotes.pdf_storage_disk', 'local');
        if (!Storage::disk($disk)->exists($note->pdf_path)) {
            return $this->errorResponse('The document file does not exist in storage', 404);
        }

        $note->increment('download_count');

        $downloadFilename = $note->file_original_name ?: ($note->slug . '.pdf');

        return Storage::disk($disk)->download($note->pdf_path, $downloadFilename, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Securely serve note thumbnail image.
     */
    public function thumbnail(int $id)
    {
        $note = $this->findPublishedNote($id);
        if (!$note || empty($note->thumbnail_path)) {
            return $this->errorResponse('No thumbnail available for this note', 404);
        }

        $disk = config('hbnotes.pdf_storage_disk', 'local');
        if (!Storage::disk($disk)->exists($note->thumbnail_path)) {
            return $this->errorResponse('The thumbnail file does not exist in storage', 404);
        }

        $fullPath = Storage::disk($disk)->path($note->thumbnail_path);

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Helper to retrieve an accessible published note with verified hierarchy.
     */
    protected function findPublishedNote(int $id): ?Note
    {
        return Note::where('status', 'published')
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
    }
}
