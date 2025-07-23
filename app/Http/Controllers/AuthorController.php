<?php

namespace App\Http\Controllers;

use App\Http\Requests\Author\UpdatePermissionRequest;
use App\Http\Requests\Author\UpdateRequest;
use App\Models\Author;
use App\Models\Country;
use App\Models\ResearchArea;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;


class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $columns = [
            'index' => [
                'label' => __('S. No.'),
                'orderable' => false,
                'searchable' => false
            ],
            'title' => [
                'label' => __('Title'),
                'orderable' => true,
                'searchable' => true
            ],
            'name' => [
                'label' => __('Name'),
                'orderable' => false,
                'searchable' => true
            ],
            'email' => [
                'label' => __('Email'),
                'orderable' => true,
                'searchable' => true
            ],
            'roles' => [
                'label' => __('roles'),
                'orderable' => false,
                'searchable' => false
            ],
            'highest_qualification' => [
                'label' => __('Qualification'),
                'orderable' => true,
                'searchable' => true
            ],
            'scopus_id' => [
                'label' => __('Scopus ID'),
                'orderable' => true,
                'searchable' => true
            ],
            'orcid_id' => [
                'label' => __('ORCID ID'),
                'orderable' => true,
                'searchable' => true
            ],
            'position' => [
                'label' => __('Position'),
                'orderable' => true,
                'searchable' => true
            ],
            'department' => [
                'label' => __('Department'),
                'orderable' => true,
                'searchable' => true
            ],
            'organization_institution' => [
                'label' => __('Organization/Institution'),
                'orderable' => true,
                'searchable' => true
            ],
            'created_at' =>   [
                'label' => __('Created on'),
                'orderable' => true,
                'searchable' => true
            ],
            'updated_at' =>   [
                'label' => __('Updated on'),
                'orderable' => true,
                'searchable' => true
            ],
            'action' => [
                'label' => __('Action'),
                'orderable' => false,
                'searchable' => false
            ]
        ];

        $data = Author::query()
            ->search(
                $request->input('search'),
                array_keys(array_filter($columns, fn ($item) => $item['searchable']))
            )
            ->when(
                $request->input('order'),
                function ($query, $order) {
                    foreach ($order as $column => $dir) {
                        $query->orderBy($column, $dir);
                    }
                    return $query;
                },
                fn ($query) => $query->latest()
            )
            ->with('roles')
            ->jsonPaginate($request->input('perPage'));

        $actions = [
            ['value' => 'edit', 'label' => 'Edit'],
            ['value' => 'permission', 'label' => 'Permission'],
            ['value' => 'confirmDelete', 'label' => 'Delete'],

        ];

        return Inertia::render('author/list', compact('columns', 'data', 'actions'));
    }

    public function edit(?Author $author)
    {
        Log::info("logging edit action");
        $author ??= new Author;
        $author->load('country', 'researchAreas');

        $author->makeVisible([
            'privacy_policy_accepted',
            'subscribed_for_notifications',
            'accept_review_request',
        ]);

        $countries = Country::query()->active()->get();
        $research_areas = ResearchArea::query()->active()->get();

        return Inertia::render('author/edit', compact('author', 'countries', 'research_areas'));
    }

    public function update(UpdateRequest $request, ?Author $author)
    {
        $page = $request->input('page', 1);
        $author ??= new Author;

        $author->fill($request->validated());
        $author->country()->associate($request->input('country'));
        $author->save();

        $author->researchAreas()->sync(
            $request->input('research_areas', [])
        );

        return to_route('authors.index', ['page' => $page])->with(
            'success',
            $author->wasRecentlyCreated
                ? __('Author created successfully.')
                : __('Author updated successfully.')
        );
    }

    public function permission(Author $author)
    {
        $author->load('roles');

        $roles = Role::query()->active()->get();

        return Inertia::render('author/permission', compact('author', 'roles'));
    }

    public function updatePermission(UpdatePermissionRequest $request, Author $author)
    {
        $page = $request->input('page', 1);
        Log::info("updatePermission page" .$page);  
        $author->roles()->sync($request->input('roles', []));

        return to_route('authors.index', ['page' => $page])->with(
            'success',
            $author->wasRecentlyCreated
                ? __('Author created successfully.')
                : __('Author updated successfully.')
        );
    }

    public function confirmDelete(Request $request, Author $author)
    {
        $page = $request->input('page', 1);
        Log::info("Confirm delete page" .$page);  
        return Inertia::render('author/confirm-delete', [
            'author' => $author,
        ]);
    }

    public function destroy(Request $request, Author $author)
    {
        $page = $request->input('page', 1);
        Log::info(" delete page" .$page);
        Log::info("Soft deleting author ID: " . $author->id);

        $author->update(['is_deleted' => true]);
    
        return to_route('authors.index', ['page' => $page])
            ->with('success', __('Author marked as deleted successfully.'));
    }
}
