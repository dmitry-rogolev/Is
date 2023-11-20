<?php 

namespace dmitryrogolev\Is\Contracts;

if (config('is.uses.levels')) {
    interface Roleable extends BaseRoleable, Levelable
    {
    
    }
} else {
    interface Roleable extends BaseRoleable
    {
    
    }
}
