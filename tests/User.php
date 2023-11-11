<?php

namespace dmitryrogolev\Is\Tests;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

if (config('is.uses.uuid') && config('is.uses.soft_deletes')) {
    class User extends BaseUser
    {
        use HasUuids, SoftDeletes;
    }
} else if (config('is.uses.uuid')) {
    class User extends BaseUser
    {
        use HasUuids;
    }
} else if (config('is.uses.soft_deletes')) {
    class User extends BaseUser
    {
        use SoftDeletes;
    }
} else {
    class User extends BaseUser
    {
        
    }
}