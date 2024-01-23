<?php 

namespace dmitryrogolev\Is\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
	use AuthorizesRequests;
	use ValidatesRequests;
}