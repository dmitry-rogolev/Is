<?php 

namespace dmitryrogolev\Is\Traits;

if (config('is.uses.levels') && config('is.uses.extend_is_method')) {
    trait HasRoles 
    {
        use AbstractHasRoles, HasLevels, ExtendIsMethod {
            AbstractHasRoles::getRoles insteadof HasLevels;
            AbstractHasRoles::attachRole insteadof HasLevels;
            AbstractHasRoles::hasOneRole insteadof HasLevels;
            AbstractHasRoles::hasAllRoles insteadof HasLevels;
        }
    }
} else if (config('is.uses.levels')) {
    trait HasRoles 
    {
        use AbstractHasRoles, HasLevels {
            AbstractHasRoles::getRoles insteadof HasLevels;
            AbstractHasRoles::attachRole insteadof HasLevels;
            AbstractHasRoles::hasOneRole insteadof HasLevels;
            AbstractHasRoles::hasAllRoles insteadof HasLevels;
        }
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
