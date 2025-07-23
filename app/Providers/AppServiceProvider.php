<?php

namespace App\Providers;

use DateTime;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Hashids::class, fn ($app) => new Hashids(
            salt: $app['config']['hashid']['salt'],
            minHashLength: $app['config']['hashid']['length'],
            alphabet: $app['config']['hashid']['alphabet'],
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!$this->app->isProduction()) {
            Model::preventLazyLoading();
            Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
                $class = get_class($model);
                info("Attempted to lazy load [{$relation}] on model [{$class}].");
            });
        }

        if ($this->app->hasDebugModeEnabled()) {
            DB::listen(fn ($query) => Log::info($this->toRawSql($query->sql, $query->bindings), [
                'time' => $query->time
            ]));
        }

        Request::macro('decodeHashId', function (...$keys) {
            $hash_id_decode = function ($item) use (&$hash_id_decode) {
                return empty($item) ? $item : (is_array($item)
                    ? array_map($hash_id_decode, $item)
                    : hash_id_decode($item));
            };

            foreach ($keys as $key) {
                $_keys = explode('.', $key, 2);
                $_key = array_shift($_keys);
                $value = $this->input($_key);

                if (count($_keys) === 0) {
                    $value = $hash_id_decode($value);
                } else {
                    $_keys = array_shift($_keys);
                    if (!empty($_value = data_get($value, $_keys))) {
                        $_value = $hash_id_decode($_value);
                        if (str_contains($_keys, '*') && is_array($_value)) {
                            foreach ($_value as $index => $__value) {
                                if (empty($__value)) continue;
                                $value = data_set($value, str_replace('*', $index, $_keys), $__value);
                            }
                        } else {
                            $value = data_set($value, $keys, $_value);
                        }
                    }
                }

                $this->merge([$_key => $value]);
            }
        });

        $this->app['validator']->extend(
            'recaptcha',
            fn ($attribute, $response) => Http::asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $this->app['config']['services']['recaptcha']['secret'],
                    'remoteip' => $this->app['request']->getClientIp(),
                    'response' => $response,
                ])
                ->json(key: 'success', default: false)
        );

        Builder::macro('jsonPaginate', function (int $perPage = null, string $pageName = 'page', int $page = null, \Closure $transform = null) {
            $page ??= request($pageName);
            $perPage ??= $this->model->getPerPage();

            $total = $this->toBase()->getCountForPagination();

            $items = $total
                ? $this->forPage($page, $perPage)->get()
                : $this->model->newCollection();

            if (!is_null($transform)) {
                $items->transform($transform);
            }

            return compact('total', 'items');
        });
    }

    protected function toRawSql(string $sql, array $bindings)
    {
        return array_reduce(
            $bindings,
            function ($sql, $binding) {
                if ($binding instanceof DateTime) {
                    $binding = $binding->format('Y-m-d H:i:s');
                }
                return preg_replace(
                    '/\?/',
                    is_string($binding) ? "'{$binding}'" : $binding,
                    str_replace('"', '', $sql),
                    1
                );
            },
            $sql
        );
    }
}
