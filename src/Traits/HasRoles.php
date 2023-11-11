<?php 

namespace dmitryrogolev\Is\Traits;

if (config('is.uses.levels') && config('is.uses.extend_is_method')) {
    trait HasRoles 
    {
        use BaseHasRoles, HasLevels, ExtendIsMethod {
            HasLevels::attachRole insteadof BaseHasRoles;
            HasLevels::hasOneRole insteadof BaseHasRoles;
            HasLevels::hasAllRoles insteadof BaseHasRoles;
        }
    }
} else if (config('is.uses.levels')) {
    trait HasRoles 
    {
        use BaseHasRoles, HasLevels {
            HasLevels::attachRole insteadof BaseHasRoles;
            HasLevels::hasOneRole insteadof BaseHasRoles;
            HasLevels::hasAllRoles insteadof BaseHasRoles;
        }
    }
} else if (config('is.uses.extend_is_method')) {
    trait HasRoles 
    {
        use BaseHasRoles, ExtendIsMethod;
    }
} else {
    trait HasRoles 
    {
        use BaseHasRoles;
    }
}