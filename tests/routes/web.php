<?php 
use Illuminate\Support\Facades\Route;

Route::get('welcome', function() {
    
});

Route::middleware('auth')->get('profile', function() {

});

Route::middleware(['auth', 'role:user'])->post('role/user', function() {

});

Route::middleware(['auth', 'role:moderator'])->post('role/moderator', function() {

});

Route::middleware(['auth', 'role:admin'])->post('role/admin', function() {

});

Route::middleware(['auth', 'role:moderator,admin'])->post('role/moderator-admin', function() {

});

if (config('is.uses.levels')) {
    Route::middleware(['auth', 'level:1'])->post('level/1', function() {

    });
    
    Route::middleware(['auth', 'level:2'])->post('level/2', function() {
    
    });
    
    Route::middleware(['auth', 'level:3'])->post('level/3', function() {
    
    });
}
