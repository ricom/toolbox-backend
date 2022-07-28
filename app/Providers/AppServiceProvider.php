<?php

namespace App\Providers;

use App\Console\Commands\PurgeAnonymousUsersCommand;
use App\OpenApi\Builder\MyPathsBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Vyuldashev\LaravelOpenApi\Builders\PathsBuilder;

/**
 * @ignore
 */
class AppServiceProvider extends ServiceProvider
{

    public $singletons = [

    ];

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        PathsBuilder::class => MyPathsBuilder::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ResponseFactory::macro('created', function (string $route_name, object $model): Response {
            return \response($model,
                Response::HTTP_CREATED,
                ['Location' => route($route_name . '.show', $model->id, false)]);
        });


        if (config("app.debug")) {
            DB::listen(function ($query) {
                Log::info(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }


    }
}
