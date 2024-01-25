<?php

namespace dmitryrogolev\Is\Actions;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeleteRole
{
    public function handle(Request $request, Model $role): mixed
    {
        Is::destroy($role);

        return config('is.uses.soft_deletes') ? new RoleResource($role) : null;
    }
}
