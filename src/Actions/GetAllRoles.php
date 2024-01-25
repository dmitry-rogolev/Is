<?php

namespace dmitryrogolev\Is\Actions;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetAllRoles
{
    public function handle(Request $request): AnonymousResourceCollection
    {
        return RoleResource::collection(Is::index());
    }
}
