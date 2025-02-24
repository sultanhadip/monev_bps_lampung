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

        Gate::define('isKepProv', function ($user) {
            return $user->role == 'Kepala BPS Provinsi';
        });


        Gate::define('isKepKaKo', function ($user) {
            return $user->role == 'Kepala BPS Kab/Kota';
        });

        Gate::define('isAdmin', function ($user) {
            return $user->role == 'Admin';
        });

        Gate::define('isNeracaProv', function ($user) {
            return $user->role == 'Neraca Provinsi';
        });

        Gate::define('isSosialProv', function ($user) {
            return $user->role == 'Sosial Provinsi';
        });

        Gate::define('isProduksiProv', function ($user) {
            return $user->role == 'Produksi Provinsi';
        });

        Gate::define('isDistribusiProv', function ($user) {
            return $user->role == 'Distribusi Provinsi';
        });

        Gate::define('isIPDSProv', function ($user) {
            return $user->role == 'IPDS Provinsi';
        });

        Gate::define('isNeracaKaKo', function ($user) {
            return $user->role == 'Neraca Kabupaten/Kota';
        });

        Gate::define('isSosialKaKo', function ($user) {
            return $user->role == 'Sosial Kabupaten/Kota';
        });

        Gate::define('isProduksiKaKo', function ($user) {
            return $user->role == 'Produksi Kabupaten/Kota';
        });

        Gate::define('isDistribusiKaKo', function ($user) {
            return $user->role == 'Distribusi Kabupaten/Kota';
        });

        Gate::define('isIPDSKaKo', function ($user) {
            return $user->role == 'IPDS Kabupaten/Kota';
        });
    }
}
