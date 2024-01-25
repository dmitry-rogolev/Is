<?php

namespace dmitryrogolev\Is\Actions;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Http\Requests\StoreRoleRequest;
use dmitryrogolev\Is\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Model;

class CreateNewRole
{
    public function handle(StoreRoleRequest $request): RoleResource|Model
    {
        $validated = $request->validated();

        $role = Is::create($validated);

        return new RoleResource($role);
    }
}
