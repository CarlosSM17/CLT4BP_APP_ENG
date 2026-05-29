<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     *
     * El instructor del curso o un admin pueden gestionar el curso.
     */
    public function manage(User $user, Course $course): bool
    {
        // Admin puede gestionar cualquier curso
        if ($user->role === 'admin') {
            return true;
        }

        // Instructor puede gestionar sus propios cursos
        if ($user->role === 'instructor' && $course->instructor_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     *
     */
    public function view(User $user, Course $course): bool
    {
        // Admin e instructor pueden ver cualquier curso
        if (in_array($user->role, ['admin', 'instructor'])) {
            return true;
        }

        // Estudiantes pueden ver cursos en los que están inscritos
        if ($user->role === 'student') {
            return $course->students()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    /**
     *
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'instructor']);
    }

    /**
     *
     */
    public function update(User $user, Course $course): bool
    {
        return $this->manage($user, $course);
    }

    /**
     *
     */
    public function delete(User $user, Course $course): bool
    {
        // Solo admin puede eliminar cursos
        return $user->role === 'admin';
    }
}
