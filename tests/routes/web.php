<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:user'])->get('role/user', function () { });
Route::middleware(['role:moderator'])->get('role/moderator', function () { });
Route::middleware(['role:editor'])->get('role/editor', function () { });
Route::middleware(['role:admin'])->get('role/admin', function () { });

Route::middleware(['is:user'])->get('is/user', function () { });
Route::middleware(['is:moderator'])->get('is/moderator', function () { });
Route::middleware(['is:editor'])->get('is/editor', function () { });
Route::middleware(['is:admin'])->get('is/admin', function () { });


Route::middleware(['role:user'])->post('role/user', function () { });
Route::middleware(['role:moderator'])->post('role/moderator', function () { });
Route::middleware(['role:editor'])->post('role/editor', function () { });
Route::middleware(['role:admin'])->post('role/admin', function () { });

Route::middleware(['is:user'])->post('is/user', function () { });
Route::middleware(['is:moderator'])->post('is/moderator', function () { });
Route::middleware(['is:editor'])->post('is/editor', function () { });
Route::middleware(['is:admin'])->post('is/admin', function () { });



Route::middleware(['role:user,moderator'])->get('role/user/moderator', function () { });
Route::middleware(['role:user,moderator,editor'])->get('role/user/moderator/editor', function () { });
Route::middleware(['role:user,moderator,editor,admin'])->get('role/user/moderator/editor/admin', function () { });

Route::middleware(['is:user,moderator'])->get('is/user/moderator', function () { });
Route::middleware(['is:user,moderator,editor'])->get('is/user/moderator/editor', function () { });
Route::middleware(['is:user,moderator,editor,admin'])->get('is/user/moderator/editor/admin', function () { });


Route::middleware(['role:user,moderator'])->post('role/user/moderator', function () { });
Route::middleware(['role:user,moderator,editor'])->post('role/user/moderator/editor', function () { });
Route::middleware(['role:user,moderator,editor,admin'])->post('role/user/moderator/editor/admin', function () { });

Route::middleware(['is:user,moderator'])->post('is/user/moderator', function () { });
Route::middleware(['is:user,moderator,editor'])->post('is/user/moderator/editor', function () { });
Route::middleware(['is:user,moderator,editor,admin'])->post('is/user/moderator/editor/admin', function () { });



Route::middleware(['level:1'])->get('level/1', function () { });
Route::middleware(['level:2'])->get('level/2', function () { });
Route::middleware(['level:3'])->get('level/3', function () { });
Route::middleware(['level:4'])->get('level/4', function () { });
Route::middleware(['level:5'])->get('level/5', function () { });


Route::middleware(['level:1'])->post('level/1', function () { });
Route::middleware(['level:2'])->post('level/2', function () { });
Route::middleware(['level:3'])->post('level/3', function () { });
Route::middleware(['level:4'])->post('level/4', function () { });
Route::middleware(['level:5'])->post('level/5', function () { });
