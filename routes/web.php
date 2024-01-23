<?php 

use dmitryrogolev\Is\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware("role:admin")->prefix("roles")->name("roles.")->group(function () {
	Route::get("/", [RoleController::class, "index"])->name("index");
	Route::get("{role}", [RoleController::class, "show"])->name("show");
	Route::post("/", [RoleController::class, "store"])->name("store");
	Route::patch("{role}", [RoleController::class, "update"])->name("update");
	Route::delete("{role}", [RoleController::class, "destroy"])->name("destroy");
	Route::patch("{id}/restore", [RoleController::class, "restore"])->name("restore");
	Route::delete("{id}/force", [RoleController::class, "forceDestroy"])->name("forceDestroy");
});
