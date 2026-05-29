<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * List assessments for a course
     */
    public function index(Request $request, Course $course)
    {
        // Check course access permissions
        if (!$this->canAccessCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'Unauthorized to view this course',
            ], 403);
        }

        $assessmentsQuery = $course->assessments()
            ->with(['responses' => function($query) use ($request) {
                if ($request->user()->isStudent()) {
                    $query->where('student_id', $request->user()->id);
                }
            }]);

        // Students only see active assessments
        if ($request->user()->isStudent()) {
            $assessmentsQuery->where('is_active', true);
        }

        $assessments = $assessmentsQuery->get();

        // Add completion info for each assessment
        $assessments->each(function ($assessment) use ($request) {
            if ($request->user()->isStudent()) {
                $assessment->is_completed = $assessment->isCompleted($request->user()->id);
                $assessment->user_response = $assessment->getResponse($request->user()->id);
            }
            $assessment->completion_rate = $assessment->completionRate();
        });

        return response()->json([
            'assessments' => $assessments,
        ]);
    }

    /**
     * Create assessment
     */
    public function store(Request $request, Course $course)
    {
        // Check permissions
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'assessment_type' => 'required|in:recall_initial,comprehension_initial,mslq_motivation_initial,mslq_strategies,recall_final,comprehension_final,cognitive_load,mslq_motivation_final,course_interest,imms',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*.id' => 'required',
            'questions.*.type' => 'required|in:multiple_choice,text,scale,likert',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'nullable|array',
            'config' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'time_limit' => 'nullable|integer|min:1',
        ]);

        $validated['course_id'] = $course->id;

        $assessment = Assessment::create($validated);

        return response()->json([
            'message' => 'Assessment created successfully',
            'assessment' => $assessment,
        ], 201);
    }

    /**
     * View specific assessment
     */
    public function show(Request $request, Course $course, Assessment $assessment)
    {
        // Verify the assessment belongs to the course
        if ($assessment->course_id !== $course->id) {
            return response()->json(['message' => 'Assessment not found'], 404);
        }

        // Check course access permissions
        if (!$this->canAccessCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'Unauthorized to view this course',
            ], 403);
        }

        // Students cannot view inactive assessments
        if ($request->user()->isStudent() && !$assessment->is_active) {
            return response()->json([
                'message' => 'This assessment is not available',
            ], 403);
        }

        $assessment->load('responses');

        // Add the student's own response if applicable
        if ($request->user()->isStudent()) {
            $assessment->is_completed = $assessment->isCompleted($request->user()->id);
            $assessment->user_response = $assessment->getResponse($request->user()->id);
        }

        return response()->json([
            'assessment' => $assessment,
        ]);
    }

    /**
     * Update assessment
     */
    public function update(Request $request, Course $course, Assessment $assessment)
    {
        // Check permissions
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify the assessment belongs to the course
        if ($assessment->course_id !== $course->id) {
            return response()->json(['message' => 'Assessment not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'sometimes|required|array',
            'config' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'time_limit' => 'nullable|integer|min:1',
        ]);

        $assessment->update($validated);

        return response()->json([
            'message' => 'Assessment updated successfully',
            'assessment' => $assessment,
        ]);
    }

    /**
     * Delete assessment
     */
    public function destroy(Request $request, Course $course, Assessment $assessment)
    {
        // Check permissions
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify the assessment belongs to the course
        if ($assessment->course_id !== $course->id) {
            return response()->json(['message' => 'Assessment not found'], 404);
        }

        $assessment->delete();

        return response()->json([
            'message' => 'Assessment deleted successfully',
        ]);
    }

    /**
     * Activate/Deactivate assessment
     */
    public function toggleActive(Request $request, Course $course, Assessment $assessment)
    {
        // Check permissions
        if (!$request->user()->isInstructor() && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assessment->update([
            'is_active' => !$assessment->is_active,
        ]);

        return response()->json([
            'message' => 'Status updated',
            'assessment' => $assessment,
        ]);
    }

    /**
     * Check if the user can access the course
     */
    private function canAccessCourse($user, $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isInstructor() && $course->instructor_id === $user->id) {
            return true;
        }

        if ($user->isStudent()) {
            // Verify the student is enrolled in the course
            return $course->enrollments()
                ->where('student_id', $user->id)
                ->where('status', 'enrolled')
                ->exists();
        }

        return false;
    }
}
