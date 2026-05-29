<?php

use App\Services\StudentProfileGeneratorService;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_complete_student_profile()
    {
        // Crear datos de prueba
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        // Crear respuestas simuladas
        $this->createMockAssessmentResponses($student->id, $course->id);

        $service = app(StudentProfileGeneratorService::class);
        $profile = $service->generateProfile($student->id, $course->id);

        $this->assertInstanceOf(StudentProfile::class, $profile);
        $this->assertEquals($student->id, $profile->student_id);
        $this->assertArrayHasKey('mslq_analysis', $profile->profile_data);
        $this->assertArrayHasKey('knowledge_assessment', $profile->profile_data);
        $this->assertArrayHasKey('profile_summary', $profile->profile_data);
    }

    private function createMockAssessmentResponses($studentId, $courseId)
    {
        // Implementar creación de datos mock
    }
}
