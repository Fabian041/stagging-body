<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
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

        // Admin selalu lolos semua gate, kecuali gate khusus super admin.
        Gate::before(function ($user, $ability) {
            if (in_array($ability, ['access-settings', 'manage-users'], true)) {
                return null;
            }

            return $user->isAdmin() ? true : null;
        });

        Gate::define('access-settings', function ($user) {
            return true;
        });

        Gate::define('manage-users', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-device-sessions', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-machine-auth', function ($user) {
            return $user->isAdmin();
        });

        // Aksi master-data & destruktif (PPIC): admin (via Gate::before) + ppic.
        // Mencakup: hapus loading list & pulling mutation, master cycle,
        // master alamat chute (CRUD/import/clear), pulling settings, hapus PIS.
        Gate::define('manage-master', function ($user) {
            return $user->hasRole('ppic');
        });
    }
}
