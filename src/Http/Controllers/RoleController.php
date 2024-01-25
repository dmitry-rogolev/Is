<?php

namespace dmitryrogolev\Is\Http\Controllers;

use dmitryrogolev\Is\Actions\CreateNewRole;
use dmitryrogolev\Is\Actions\DeleteRole;
use dmitryrogolev\Is\Actions\ForceDeleteRole;
use dmitryrogolev\Is\Actions\GetAllRoles;
use dmitryrogolev\Is\Actions\RestoreRole;
use dmitryrogolev\Is\Actions\ShowRole;
use dmitryrogolev\Is\Actions\UpdateRole;
use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Http\Requests\StoreRoleRequest;
use dmitryrogolev\Is\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request, GetAllRoles $getAllRoles): mixed
    {
        return $getAllRoles->handle($request);
    }

    public function show(Request $request, mixed $role, ShowRole $showRole): mixed
    {
        $role = Is::findOrFail($role);

        return $showRole->handle($request, $role);
    }

    public function store(StoreRoleRequest $request, CreateNewRole $createNewRole): mixed
    {
        return $createNewRole->handle($request);
    }

    public function update(UpdateRoleRequest $request, mixed $role, UpdateRole $updateRole): mixed
    {
        $role = Is::findOrFail($role);

        return $updateRole->handle($request, $role);
    }

    public function destroy(Request $request, mixed $role, DeleteRole $deleteRole): mixed
    {
        $role = Is::findOrFail($role);

        return $deleteRole->handle($request, $role) ?: response()->noContent();
    }

    public function restore(Request $request, mixed $role, RestoreRole $restoreRole): mixed
    {
        $role = Is::getModel()::onlyTrashed()->findOrFail($role);

        return $restoreRole->handle($request, $role);
    }

    public function forceDestroy(Request $request, mixed $role, ForceDeleteRole $forceDelete): mixed
    {
        $role = Is::getModel()::withTrashed()->findOrFail($role);

        $forceDelete->handle($request, $role);

        return response()->noContent();
    }
}
