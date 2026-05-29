<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Course;
use App\Policies\UserPolicy;
use App\Policies\CoursePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Course::class => CoursePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
