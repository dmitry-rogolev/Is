<?php 

namespace dmitryrogolev\Is\Tests;

use dmitryrogolev\Is\Models\Role as Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Role extends Model 
{
    /**
     * Роль относится к множеству пользователей
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): MorphToMany 
    {
        return $this->roleables(config('is.models.user'));
    }
}
