<?php

use App\Models\User;
use App\Models\Course;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_generate_student_profile()
    {
        /** @var User $instructor */
        $instructor = User::factory()->create(['role' => 'instructor']);
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $response = $this->actingAs($instructor)
            ->postJson("/api/courses/{$course->id}/profiles/students/{$student->id}/generate");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'student_id',
                    'course_id',
                    'profile_data',
                    'generated_at'
                ]
            ]);
    }

    public function test_student_cannot_generate_other_student_profile()
    {
        /** @var User $student1 */
        $student1 = User::factory()->create(['role' => 'student']);
        /** @var User $student2 */
        $student2 = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $response = $this->actingAs($student1)
            ->postJson("/api/courses/{$course->id}/profiles/students/{$student2->id}/generate");

        $response->assertStatus(403);
    }
}
