<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        //        Gate::before(function (User $user, string $ability) {
        //            if ($user->is_manager) {
        //                return true;
        //            }
        //        });
        Gate::define('add-inventory', function (
            User $user,
            Inventory $inventory
        ) {
            return $user->managing_token === $inventory->owner_token;
        });
    }
}
