<?php

namespace App\Http\Middleware;

use App\Enums\Manuscript\Action;
use App\Enums\Manuscript\Filter;
use App\Enums\Section;
use App\Models\Manuscript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'url' => fn () => $this->url($request),
            'flash' => fn () => $this->flash($request),
            'auth' => fn () => $this->auth($request),
            'header' => fn () => $this->header($request),
            'aside' => fn () => $this->aside($request),
            'search' => Inertia::lazy(fn () => $this->search($request)),
        ]);
    }

    public function url(Request $request)
    {
        return [
            'current' => url()->current(),
            'previous' => url()->previous(),
        ];
    }

    public function auth(Request $request)
    {
        $user = $request->user();

        return compact('user');
    }

    public function header(Request $request)
    {
        if (is_null($user = $request->user())) return;

        $items = array_values(array_filter(
            Section::cases(),
            fn (Section $section) => $user->roles->map->section->contains($section)
        ));

        return compact('items');
    }

    public function aside(Request $request)
    {
        if (is_null($user = $request->user())) return;

        $items = array_filter(
            $user->section->items($user),
            fn ($item) => $user->can($item['permission'])
        );

        array_unshift($items, ['label' => 'Home', 'route' => 'index']);
        array_push($items, ['label' => 'Update Profile', 'route' => 'profile']);

        $items = array_map(function ($item) use ($user, $request) {
            ['label' => $label, 'route' => $route] = $item;
            $path = route(...($route = is_array($route) ? $route : [$route]), absolute: false);

            $active = Route::is('manuscripts.action') && $request->route('action') === Action::VIEW
                ? URL::previousPath() === $path
                : "/{$request->path()}" === $path;

            $badge = isset($route[0], $route[1], $route[1]['filter']) && $route[0] === 'manuscripts.index' && $route[1]['filter'] instanceof Filter
                ? Manuscript::query()->filterForUserAndStatus(user: $user, filter: $route[1]['filter'])->count()
                : null;

            return compact('label', 'path', 'badge', 'active');
        }, $items);

        return compact('items');
    }

    private function flash(Request $request): ?array
    {
        if (!$request->hasSession()) return null;

        if ($request->session()->has('errors')) {
            return [
                'type' => 'error',
                'message' => __('One or more validation errors occurred.')
            ];
        }

        foreach (['success', 'error'] as $type) {
            if (!$request->session()->has($type)) continue;
            return [
                'type' => $type,
                'message' => $request->session()->get($type)
            ];
        }

        return null;
    }

    public function search(Request $request): array
    {
        $items = ($search = $request->input('q'))
            ? Manuscript::query()
            ->with('revision:id,index,title')
            ->orWhere('code', 'LIKE', "%{$search}%")
            ->orWhereHas(
                'revision',
                fn ($query) => $query->Where('title', 'LIKE', "%{$search}%")
            )
            ->limit(10)
            ->get()
            : [];

        return compact('items');
    }
}
