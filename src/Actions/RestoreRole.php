<?php 

namespace dmitryrogolev\Is\Actions;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RestoreRole
{
	public function handle(Request $request, Model $role): RoleResource
	{
		Is::restore($role);

		return new RoleResource($role);
	}
}
