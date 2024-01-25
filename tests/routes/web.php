<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => true)->name('welcome');
Route::get('login', fn () => true)->name('login');

Route::middleware(['role:user'])->get('role/user', fn () => true);
Route::middleware(['role:moderator'])->get('role/moderator', fn () => true);
Route::middleware(['role:editor'])->get('role/editor', fn () => true);
Route::middleware(['role:admin'])->get('role/admin', fn () => true);

Route::middleware(['is:user'])->get('is/user');
Route::middleware(['is:moderator'])->get('is/moderator', fn () => true);
Route::middleware(['is:editor'])->get('is/editor', fn () => true);
Route::middleware(['is:admin'])->get('is/admin', fn () => true);

Route::middleware(['role:user'])->post('role/user', fn () => true);
Route::middleware(['role:moderator'])->post('role/moderator', fn () => true);
Route::middleware(['role:editor'])->post('role/editor', fn () => true);
Route::middleware(['role:admin'])->post('role/admin', fn () => true);

Route::middleware(['is:user'])->post('is/user', fn () => true);
Route::middleware(['is:moderator'])->post('is/moderator', fn () => true);
Route::middleware(['is:editor'])->post('is/editor', fn () => true);
Route::middleware(['is:admin'])->post('is/admin', fn () => true);

Route::middleware(['role:user,moderator'])->get('role/user/moderator', fn () => true);
Route::middleware(['role:user,moderator,editor'])->get('role/user/moderator/editor', fn () => true);
Route::middleware(['role:user,moderator,editor,admin'])->get('role/user/moderator/editor/admin', fn () => true);

Route::middleware(['is:user,moderator'])->get('is/user/moderator', fn () => true);
Route::middleware(['is:user,moderator,editor'])->get('is/user/moderator/editor', fn () => true);
Route::middleware(['is:user,moderator,editor,admin'])->get('is/user/moderator/editor/admin', fn () => true);

Route::middleware(['role:user,moderator'])->post('role/user/moderator', fn () => true);
Route::middleware(['role:user,moderator,editor'])->post('role/user/moderator/editor', fn () => true);
Route::middleware(['role:user,moderator,editor,admin'])->post('role/user/moderator/editor/admin', fn () => true);

Route::middleware(['is:user,moderator'])->post('is/user/moderator', fn () => true);
Route::middleware(['is:user,moderator,editor'])->post('is/user/moderator/editor', fn () => true);
Route::middleware(['is:user,moderator,editor,admin'])->post('is/user/moderator/editor/admin', fn () => true);

Route::middleware(['level:1'])->get('level/1', fn () => true);
Route::middleware(['level:2'])->get('level/2', fn () => true);
Route::middleware(['level:3'])->get('level/3', fn () => true);
Route::middleware(['level:4'])->get('level/4', fn () => true);
Route::middleware(['level:5'])->get('level/5', fn () => true);

Route::middleware(['level:1'])->post('level/1', fn () => true);
Route::middleware(['level:2'])->post('level/2', fn () => true);
Route::middleware(['level:3'])->post('level/3', fn () => true);
Route::middleware(['level:4'])->post('level/4', fn () => true);
Route::middleware(['level:5'])->post('level/5', fn () => true);
