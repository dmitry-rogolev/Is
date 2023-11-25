<?php 

namespace dmitryrogolev\Is\Contracts;

if (config('is.uses.levels')) {
    interface Roleable extends Levelable
    {
        
    }
} else {
    interface Roleable extends AbstractRoleable 
    {
        
    }
}
