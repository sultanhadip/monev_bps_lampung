<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('canExport', function ($user) {
            // Check if the user's role matches one of the allowed roles
            return in_array($user->role, ['Admin', 'Admin Provinsi', 'Kepala BPS']);
        });

        Gate::define('isKepBPS', function ($user) {
            return $user->role == 'Kepala BPS';
        });

        Gate::define('isAdmin', function ($user) {
            return $user->role == 'Admin';
        });

        Gate::define('isAdminSatker', function ($user) {
            return $user->role == 'Admin Satuan Kerja';
        });

        Gate::define('isAdminProv', function ($user) {
            return $user->role == 'Admin Provinsi';
        });

        Gate::define('isOperator', function ($user) {
            return $user->role == 'Operator';
        });
    }
}
