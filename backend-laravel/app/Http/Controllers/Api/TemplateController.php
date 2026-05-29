<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * GET /assessment-templates
     * List all available assessment templates
     */
    public function index(Request $request)
    {
        $templates = Assessment::templates()
            ->select([
                'id',
                'assessment_type',
                'title',
                'description',
                'config',
                'time_limit',
                'requires_manual_grading',
            ])
            ->withCount('derivedAssessments as usage_count')
            ->get();

        // Add question count
        $templates->each(function ($template) {
            $template->questions_count = count($template->questions ?? []);
        });

        // Group by assessment type
        $grouped = $templates->groupBy('assessment_type')->map(function ($group) {
            return $group->values();
        });

        return response()->json([
            'templates' => $templates,
            'grouped' => $grouped,
            'count' => $templates->count(),
        ]);
    }

    /**
     * GET /assessment-templates/{template}
     * Show details of a specific template
     */
    public function show(Request $request, Assessment $template)
    {
        if (!$template->is_template) {
            return response()->json([
                'message' => 'Not a valid template',
            ], 404);
        }

        // Extract dimension information
        $dimensions = $this->extractDimensions($template);

        // Question count by type
        $questionTypes = collect($template->questions ?? [])
            ->groupBy('type')
            ->map(fn($group) => $group->count());

        return response()->json([
            'template' => $template,
            'questions_count' => count($template->questions ?? []),
            'dimensions' => $dimensions,
            'question_types' => $questionTypes,
            'usage_count' => $template->derivedAssessments()->count(),
        ]);
    }

    /**
     * POST /courses/{course}/assessments/from-template/{template}
     * Create an assessment from a template
     */
    public function createFromTemplate(Request $request, Course $course, Assessment $template)
    {
        // Check permissions
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'Unauthorized to manage this course',
            ], 403);
        }

        // Verify it is a valid template
        if (!$template->is_template) {
            return response()->json([
                'message' => 'Not a valid template',
            ], 400);
        }

        // Check if an assessment of this type already exists in the course
        $existingAssessment = Assessment::where('course_id', $course->id)
            ->where('assessment_type', $template->assessment_type)
            ->first();

        if ($existingAssessment) {
            return response()->json([
                'message' => 'An assessment of this type already exists in the course',
                'existing_assessment' => [
                    'id' => $existingAssessment->id,
                    'title' => $existingAssessment->title,
                    'is_active' => $existingAssessment->is_active,
                ],
            ], 409);
        }

        // Validate optional customization fields
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'config' => 'nullable|array',
        ]);

        try {
            $assessment = Assessment::createFromTemplate(
                $template->id,
                $course->id,
                $validated
            );

            return response()->json([
                'message' => 'Assessment created successfully from template',
                'assessment' => $assessment,
                'source_template' => [
                    'id' => $template->id,
                    'title' => $template->title,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating assessment from template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /courses/{course}/available-templates
     * List templates available for a course (excluding already used ones)
     */
    public function availableForCourse(Request $request, Course $course)
    {
        // Check permissions
        if (!$this->canManageCourse($request->user(), $course)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get assessment types already existing in the course
        $existingTypes = Assessment::where('course_id', $course->id)
            ->pluck('assessment_type')
            ->toArray();

        // Get templates that have not been used yet
        $availableTemplates = Assessment::templates()
            ->whereNotIn('assessment_type', $existingTypes)
            ->select(['id', 'assessment_type', 'title', 'description', 'time_limit'])
            ->get();

        // Get already used templates (for reference)
        $usedTemplates = Assessment::templates()
            ->whereIn('assessment_type', $existingTypes)
            ->select(['id', 'assessment_type', 'title'])
            ->get();

        return response()->json([
            'available_templates' => $availableTemplates,
            'used_templates' => $usedTemplates,
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
        ]);
    }

    /**
     * Extract dimension information from the template questions
     */
    private function extractDimensions(Assessment $template): array
    {
        $dimensions = [];

        foreach ($template->questions ?? [] as $question) {
            if (isset($question['dimension'])) {
                $dim = $question['dimension'];
                if (!isset($dimensions[$dim])) {
                    $dimensions[$dim] = [
                        'name' => $dim,
                        'label' => $template->config['dimensions'][$dim]['label'] ?? ucwords(str_replace('_', ' ', $dim)),
                        'items' => [],
                        'count' => 0,
                    ];
                }
                $dimensions[$dim]['items'][] = $question['item_number'] ?? $question['id'];
                $dimensions[$dim]['count']++;
            }
        }

        return array_values($dimensions);
    }

    /**
     * Check if the user can manage the course
     */
    private function canManageCourse($user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isInstructor() && $course->instructor_id === $user->id) {
            return true;
        }

        return false;
    }
}
