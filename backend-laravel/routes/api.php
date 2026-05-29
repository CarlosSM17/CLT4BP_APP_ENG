<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\StudentResponseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\GradingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CltEffectsController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\Api\DataCollectionController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes - Health Check
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

/*
|--------------------------------------------------------------------------
| API Routes - Authentication
|--------------------------------------------------------------------------
*/

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Usuarios
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('role:admin,instructor');

    Route::get('/users/{user}', [UserController::class, 'show']);

    Route::put('/users/{user}', [UserController::class, 'update']);

    Route::post('/users/instructors', [UserController::class, 'createInstructor'])
        ->middleware('role:admin');

    Route::delete('/users/{user}/deactivate', [UserController::class, 'deactivate'])
        ->middleware('role:admin');

    /*
    |--------------------------------------------------------------------------
    | Rutas de Cursos
    |--------------------------------------------------------------------------
    */

    // Ver curso específico - Accesible para usuarios inscritos
    Route::get('/courses/{course}', [CourseController::class, 'show']);

    // CRUD de cursos - Solo admin e instructor
    Route::apiResource('courses', CourseController::class)
        ->except(['show'])
        ->middleware('role:admin,instructor');

    // Inscripciones
    Route::prefix('courses/{course}')->group(function () {
        Route::post('/enroll', [EnrollmentController::class, 'enroll'])
            ->middleware('role:admin,instructor');

        Route::get('/students', [EnrollmentController::class, 'getStudents'])
            ->middleware('role:admin,instructor');

        Route::delete('/students/{student}', [EnrollmentController::class, 'unenroll'])
            ->middleware('role:admin,instructor');
    });

    // Mis inscripciones (para estudiantes)
    Route::get('/my-enrollments', [EnrollmentController::class, 'myEnrollments'])
        ->middleware('role:student');

    /*
    |--------------------------------------------------------------------------
    | Rutas de Evaluaciones
    |--------------------------------------------------------------------------
    */

    Route::prefix('courses/{course}')->group(function () {
        // CRUD de evaluaciones (instructor)
        Route::apiResource('assessments', AssessmentController::class)
            ->except(['index', 'show']);

        // Ver evaluaciones del curso (todos los roles)
        Route::get('/assessments', [AssessmentController::class, 'index']);
        Route::get('/assessments/{assessment}', [AssessmentController::class, 'show']);

        // Activar/desactivar evaluación
        Route::post('/assessments/{assessment}/toggle', [AssessmentController::class, 'toggleActive'])
            ->middleware('role:admin,instructor');

        // Respuestas de estudiantes
        Route::prefix('assessments/{assessment}')->group(function () {
            // Estudiante: iniciar, guardar, ver su respuesta
            Route::post('/start', [StudentResponseController::class, 'start'])
                ->middleware('role:student');

            Route::post('/save', [StudentResponseController::class, 'save'])
                ->middleware('role:student');

            Route::get('/my-response', [StudentResponseController::class, 'show'])
                ->middleware('role:student');

            // Instructor: ver todas las respuestas
            Route::get('/responses', [StudentResponseController::class, 'assessmentResponses'])
                ->middleware('role:admin,instructor');
        });

        // Mis respuestas en el curso (estudiante)
        Route::get('/my-responses', [StudentResponseController::class, 'myResponses'])
            ->middleware('role:student');
    });
    /*
    |--------------------------------------------------------------------------
    | Rutas de Perfiles de Estudiantes (Sprint 4)
    |--------------------------------------------------------------------------
    */

    Route::prefix('courses/{course}/profiles')->middleware('role:admin,instructor')->group(function () {
        // Consulta de perfiles
        Route::get('/students', [ProfileController::class, 'getCourseProfiles']);
        Route::get('/students/{studentId}', [ProfileController::class, 'getStudentProfile']);
        Route::get('/group', [ProfileController::class, 'getGroupProfile']);

        // Generación de perfiles
        Route::post('/students/{studentId}/generate', [ProfileController::class, 'generateStudentProfile']);
        Route::post('/students/generate-all', [ProfileController::class, 'generateAllStudentProfiles']);
        Route::post('/group/generate', [ProfileController::class, 'generateGroupProfile']);
        Route::post('/regenerate-all', [ProfileController::class, 'regenerateAllProfiles']);
    });

    /*
    |--------------------------------------------------------------------------
    | Rutas de Plantillas de Evaluaciones
    |--------------------------------------------------------------------------
    */

    // Listar y ver plantillas (accesible para instructor/admin)
    Route::get('/assessment-templates', [TemplateController::class, 'index']);
    Route::get('/assessment-templates/{template}', [TemplateController::class, 'show']);

    // Crear evaluación desde plantilla
    Route::post('/courses/{course}/assessments/from-template/{template}', [TemplateController::class, 'createFromTemplate'])
        ->middleware('role:admin,instructor');

    // Ver plantillas disponibles para un curso
    Route::get('/courses/{course}/available-templates', [TemplateController::class, 'availableForCourse'])
        ->middleware('role:admin,instructor');

    /*
    |--------------------------------------------------------------------------
    | Rutas de Calificación Manual
    |--------------------------------------------------------------------------
    */

    Route::prefix('courses/{course}')->middleware('role:admin,instructor')->group(function () {
        // Resumen de calificaciones pendientes del curso
        Route::get('/pending-grading', [GradingController::class, 'coursePendingGrading']);

        // Recalcular estados de calificación
        Route::post('/recalculate-grading', [GradingController::class, 'recalculateGradingStatus']);

        // Calificación por evaluación
        Route::prefix('assessments/{assessment}')->group(function () {
            // Ver respuestas pendientes de calificación
            Route::get('/pending-grading', [GradingController::class, 'pendingGrading']);

            // Ver una respuesta específica para calificar
            Route::get('/responses/{response}/grading', [GradingController::class, 'showResponse']);

            // Enviar calificación manual
            Route::post('/responses/{response}/grade', [GradingController::class, 'submitGrade']);

            // Revertir calificación manual
            Route::post('/responses/{response}/revert-grade', [GradingController::class, 'revertGrade']);
        });
    });
    Route::prefix('courses/{courseId}/clt-effects')->group(function () {
        Route::get('/available', [CltEffectsController::class, 'getAvailableEffects']);
        Route::get('/selection', [CltEffectsController::class, 'getSelection']);
        Route::post('/selection', [CltEffectsController::class, 'saveSelection']);
        Route::get('/recommendations', [CltEffectsController::class, 'getRecommendations']);
    });
    Route::prefix('courses/{courseId}/materials')->group(function () {
        // Student routes (antes de {materialId} para evitar conflicto)
        Route::get('/student/list', [MaterialController::class, 'studentMaterials'])
            ->middleware('role:student');

        // Instructor routes
        Route::post('/generate', [MaterialController::class, 'generate']);
        Route::get('/', [MaterialController::class, 'listMaterials']);
        Route::get('/{materialId}', [MaterialController::class, 'getMaterial']);
        Route::post('/{materialId}/toggle-active', [MaterialController::class, 'toggleActive']);
        Route::put('/{materialId}/timer', [MaterialController::class, 'updateTimer']);
        Route::get('/{materialId}/access-logs', [MaterialController::class, 'getAccessLogs']);

        // Student access logging
        Route::post('/{materialId}/access/start', [MaterialController::class, 'logAccessStart'])
            ->middleware('role:student');
        Route::post('/{materialId}/access/complete', [MaterialController::class, 'logAccessComplete'])
            ->middleware('role:student');
    });
    Route::prefix('courses/{courseId}/reports')->group(function () {
        Route::get('/instructor', [ReportController::class, 'instructorReport'])
            ->middleware('role:instructor,admin');
        Route::get('/my-report', [ReportController::class, 'studentReport'])
            ->middleware('role:student');
    });

    Route::prefix('courses/{courseId}/data-collection')->middleware('role:instructor,admin')->group(function () {
        Route::get('/summary', [DataCollectionController::class, 'summary']);
        Route::get('/pre-post-comparison', [DataCollectionController::class, 'prePostComparison']);
        Route::get('/export', [DataCollectionController::class, 'exportCsv']);
        Route::get('/student/{studentId}', [DataCollectionController::class, 'studentDetail']);
    });
});
