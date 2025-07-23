<?php

namespace App\Http\Controllers;

use App\Enums\Section;
use App\Http\Requests\Role\UpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $columns = [
            'index' => [
                'label' => __('S. No.'),
                'orderable' => false,
                'searchable' => false
            ],
            'name' => [
                'label' => __('Name'),
                'orderable' => true,
                'searchable' => true
            ],
            'section' => [
                'label' => __("Section's"),
                'orderable' => true,
                'searchable' => true
            ],
            'default' => [
                'label' => __('Is Default'),
                'orderable' => true,
                'searchable' => false
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

        $data = Role::query()
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
            ->jsonPaginate($request->input('perPage'));

        return Inertia::render('role/list', compact('columns', 'data'));
    }

    public function edit(?Role $role)
    {
        $role ??= new Role;
        $role?->load('permissions');

        $sections = Section::cases();
        $permissions = Permission::query()->get();

        return Inertia::render('role/edit', compact('role', 'sections', 'permissions'));
    }

    public function update(UpdateRequest $request, ?Role $role)
    {
        $role ??= new Role;

        $role->fill($request->only('name', 'section'));
        $role->default = $request->boolean('default');
        $role->save();

        $role->permissions()->sync($request->input('permissions', []));

        return to_route('roles.index')->with(
            'success',
            $role->wasRecentlyCreated
                ? __('Role created successfully.')
                : __('Role updated successfully.')
        );
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return to_route('roles.index')
            ->with('success', __('Role deleted successfully.'));
    }
}
