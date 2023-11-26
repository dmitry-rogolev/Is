<?php

namespace dmitryrogolev\Is\Tests\Models;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Models\Role as Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Role extends Model
{
    /**
     * Роль относится ко множеству пользователей.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->roleables(Is::userModel());
    }
}
