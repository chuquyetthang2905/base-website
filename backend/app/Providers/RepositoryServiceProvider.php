<?php

namespace App\Providers;

use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     *
     * Each line tells Laravel's IoC container:
     * "When someone type-hints this Interface, inject this concrete class."
     *
     * This is what makes Dependency Inversion work in practice —
     * Services never import or instantiate Repository classes directly.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
