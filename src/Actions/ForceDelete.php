<?php 

namespace dmitryrogolev\Is\Actions;

use dmitryrogolev\Is\Facades\Is;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ForceDelete 
{
	public function handle(Request $request, Model $role): void 
	{
		Is::forceDestroy($role);
	}
}
