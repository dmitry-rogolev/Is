<?php 

namespace dmitryrogolev\Is\Http\Controllers;

use dmitryrogolev\Is\Actions\CreateNewRole;
use dmitryrogolev\Is\Actions\DeleteRole;
use dmitryrogolev\Is\Actions\ForceDelete;
use dmitryrogolev\Is\Actions\GetAllRoles;
use dmitryrogolev\Is\Actions\RestoreRole;
use dmitryrogolev\Is\Actions\ShowRole;
use dmitryrogolev\Is\Actions\UpdateRole;
use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Http\Requests\StoreRoleRequest;
use dmitryrogolev\Is\Http\Requests\UpdateRoleRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RoleController extends Controller 
{
	public function index(Request $request, GetAllRoles $getAllRoles): mixed
	{
		return $getAllRoles->handle($request);
	}

	public function show(Request $request, Model $role, ShowRole $showRole): mixed 
	{
		return $showRole->handle($request, $role);
	}

	public function store(StoreRoleRequest $request, CreateNewRole $createNewRole): mixed 
	{
		return $createNewRole->handle($request);
	}

	public function update(UpdateRoleRequest $request, Model $role, UpdateRole $updateRole): mixed 
	{
		return $updateRole->handle($request, $role);
	}

	public function destroy(Request $request, Model $role, DeleteRole $deleteRole): mixed 
	{
		return $deleteRole->handle($request, $role) ?: response()->noContent() ;
	}

	public function restore(Request $request, int $id, RestoreRole $restoreRole): mixed 
	{
		$role = Is::getModel()::onlyTrashed()->find($id);

		if (is_null($role)) {
			abort(404);
		}

		return $restoreRole->handle($request, $role);
	}

	public function forceDestroy(Request $request, Model|int $role, ForceDelete $forceDelete): mixed
	{
		$id = is_int($role) ? $role : $role->id;

		$role = Is::getModel()::onlyTrashed()->find($id);

		if (is_null($role)) {
			abort(404);
		}

		return response()->noContent();
	}
}
