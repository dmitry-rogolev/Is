<?php

namespace dmitryrogolev\Is\Actions;

use dmitryrogolev\Is\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ShowRole
{
    public function handle(Request $request, Model $role): RoleResource|Model
    {
        return new RoleResource($role);
    }
}
