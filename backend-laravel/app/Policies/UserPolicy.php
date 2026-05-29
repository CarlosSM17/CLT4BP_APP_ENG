<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Crear usuario (instructores)
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Ver cualquier usuario
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isInstructor();
    }

    /**
     * Ver usuario específico
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id ||
               $user->isAdmin() ||
               $user->isInstructor();
    }

    /**
     * Actualizar usuario
     */
    public function update(User $user, User $model): bool
    {
        // Usuarios pueden actualizar su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        // Admins pueden actualizar cualquiera
        return $user->isAdmin();
    }

    /**
     * Eliminar/desactivar usuario
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
