<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:user'])->get('role/user');
Route::middleware(['role:moderator'])->get('role/moderator');
Route::middleware(['role:editor'])->get('role/editor');
Route::middleware(['role:admin'])->get('role/admin');

Route::middleware(['is:user'])->get('is/user');
Route::middleware(['is:moderator'])->get('is/moderator');
Route::middleware(['is:editor'])->get('is/editor');
Route::middleware(['is:admin'])->get('is/admin');

Route::middleware(['role:user'])->post('role/user');
Route::middleware(['role:moderator'])->post('role/moderator');
Route::middleware(['role:editor'])->post('role/editor');
Route::middleware(['role:admin'])->post('role/admin');

Route::middleware(['is:user'])->post('is/user');
Route::middleware(['is:moderator'])->post('is/moderator');
Route::middleware(['is:editor'])->post('is/editor');
Route::middleware(['is:admin'])->post('is/admin');

Route::middleware(['role:user,moderator'])->get('role/user/moderator');
Route::middleware(['role:user,moderator,editor'])->get('role/user/moderator/editor');
Route::middleware(['role:user,moderator,editor,admin'])->get('role/user/moderator/editor/admin');

Route::middleware(['is:user,moderator'])->get('is/user/moderator');
Route::middleware(['is:user,moderator,editor'])->get('is/user/moderator/editor');
Route::middleware(['is:user,moderator,editor,admin'])->get('is/user/moderator/editor/admin');

Route::middleware(['role:user,moderator'])->post('role/user/moderator');
Route::middleware(['role:user,moderator,editor'])->post('role/user/moderator/editor');
Route::middleware(['role:user,moderator,editor,admin'])->post('role/user/moderator/editor/admin');

Route::middleware(['is:user,moderator'])->post('is/user/moderator');
Route::middleware(['is:user,moderator,editor'])->post('is/user/moderator/editor');
Route::middleware(['is:user,moderator,editor,admin'])->post('is/user/moderator/editor/admin');

Route::middleware(['level:1'])->get('level/1');
Route::middleware(['level:2'])->get('level/2');
Route::middleware(['level:3'])->get('level/3');
Route::middleware(['level:4'])->get('level/4');
Route::middleware(['level:5'])->get('level/5');

Route::middleware(['level:1'])->post('level/1');
Route::middleware(['level:2'])->post('level/2');
Route::middleware(['level:3'])->post('level/3');
Route::middleware(['level:4'])->post('level/4');
Route::middleware(['level:5'])->post('level/5');
