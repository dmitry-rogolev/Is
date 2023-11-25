<?php 

namespace dmitryrogolev\Is\Traits;

if (config('is.uses.levels') && config('is.uses.extend_is_method')) {
    trait HasRoles 
    {
        use HasLevels, ExtendIsMethod;
    }
} else if (config('is.uses.levels')) {
    trait HasRoles 
    {
        use HasLevels;
    }
} else if (config('is.uses.extend_is_method')) {
    trait HasRoles 
    {
        use AbstractHasRoles, ExtendIsMethod;
    }
} else {
    trait HasRoles 
    {
        use AbstractHasRoles;
    }
}
