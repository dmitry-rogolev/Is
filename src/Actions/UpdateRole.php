<?php

namespace dmitryrogolev\Is\Actions;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Http\Requests\UpdateRoleRequest;
use dmitryrogolev\Is\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Model;

class UpdateRole
{
    public function handle(UpdateRoleRequest $request, Model $role): RoleResource
    {
        $validated = $request->validated();

        Is::update($role, $validated);

        return new RoleResource($role);
    }
}
