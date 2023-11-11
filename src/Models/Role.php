<?php

namespace dmitryrogolev\Is\Models;

use dmitryrogolev\Is\Models\BaseRole as Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель роли
 */
if (config('is.uses.uuid') && config('is.uses.soft_deletes')) {
    class Role extends Model
    {
        use HasUuids, SoftDeletes;
    }
} else if (config('is.uses.uuid')) {
    class Role extends Model
    {
        use HasUuids;
    }
} else if (config('is.uses.soft_deletes')) {
    class Role extends Model
    {
        use SoftDeletes;
    }
} else {
    class Role extends Model
    {
        
    }
}
