# Is

Функционал ролей для фреймворка Laravel.

## Содержание

1. [Установка](#установка)
    
    - [Composer](#composer) 
    - [Публикация ресурсов](#публикация-ресурсов)
    - [Добавление функционала в модель](#добавление-функционала-в-модель)
    - [Миграции и сидеры](#миграции-и-сидеры)
    - [Миграции](#миграции)

2. [Применение](#применение)

    - [Создание ролей](#создание-ролей)
    - [Прикрепление, отсоединение и синхронизация ролей](#прикрепление-отсоединение-и-синхронизация-ролей)
    - [Проверка ролей](#проверка-ролей)
    - [Уровни ролей](#уровни-ролей)
    - [Расширения Blade](#расширения-blade)
    - [Посредники](#посредники)

4. [Титры](#титры)
5. [Лицензия](#лицензия)

## Установка 

### Composer

Добавьте ссылку на репозиторий в файл `composer.json`

    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:dmitry-rogolev/Is.git"
        }
    ]

Подключите пакет с помощью команды: 

    composer require dmitryrogolev/is

### Публикация ресурсов

#### Публикация всех ресурсов

    php artisan is:install 

#### Публикация ресурсов по отдельности

Конфигурация

    php artisan is:install --config

Миграции

    php artisan is:install --migrations

Сидеры 

    php artisan is:install --seeders

### Добавление функционала в модель

Включите трейт `dmitryrogolev\Is\Traits\HasRoles` и реализуйте интерфейс `dmitryrogolev\Is\Contracts\Roleable` в модели.

    <?php

    namespace App\Models;

    // use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Laravel\Sanctum\HasApiTokens;
    use dmitryrogolev\Is\Contracts\Roleable;
    use dmitryrogolev\Is\Traits\HasRoles;

    class User extends Authenticatable implements Roleable 
    {
        use HasApiTokens, 
            HasFactory, 
            Notifiable, 
            HasRoles;

Трейт `dmitryrogolev\Is\Traits\HasRoles` добавляет модели возможность работы с ролями.

### Миграции 

Создайте таблицы в базе данных

    php artisan migrate

## Применение

### Создание ролей 

    $adminRole = config('is.models.role')::create([
        'name' => 'Admin',
        'slug' => 'admin',
        'description' => '',
        'level' => 5,
    ]);

    $moderatorRole = config('is.models.role')::create([
        'name' => 'Forum Moderator',
        'slug' => 'forum.moderator',
    ]);

### Прикрепление, отсоединение и синхронизация ролей 

    $user = config('is.models.user')::find($id);

    $user->attachRole('moderator'); // Присоединяем роль
    $user->detachRole($adminRole); // Отсоединяем роль
    $user->detachAllRoles(); // Отсоединяем все роли
    $user->syncRoles(['admin', 'moderator', 'user']); // Синхронизируем роли

### Проверка ролей

Проверка наличия у пользователя хотябы одной роли

    if ($user->is('admin')) {
        // 
    }

    if ($user->hasRole([$role, 24, 56])) {
        // 
    }

    if ($user->hasOneRole('user,moderator,23,456')) {
        // 
    }

    if ($user->isAdmin()) {
        // Магический метод
    }

Проверка наличия нескольких ролей

    if ($user->is(['admin', 'moderator'], true)) {
        // 
    }

    if ($user->hasRole('admin|moderator|787', true)) {
        // 
    }

    if ($user->hasAllRoles('admin', 567, $role)) {
        // 
    }

### Уровни ролей

Уровни ролей создают иерархию ролей.

    if ($user->level() > 4) {
        //
    }

### Расширения Blade 

    @role('admin') // @if(Auth::check() && Auth::user()->hasRole('admin'))
        // у пользователя есть роль admin
    @endrole

    @level(2) // @if(Auth::check() && Auth::user()->level() >= 2)
        // у пользователя уровень 2 или выше
    @endlevel

### Посредники 

Вы можете защитить роуты

    Route::get('/', function () {
        //
    })->middleware('role:admin');

    Route::get('/', function () {
        //
    })->middleware('level:2'); // level >= 2

    Route::get('/', function () {
        //
    })->middleware('role:admin', 'level:2'); // level >= 2 and Admin

    Route::group(['middleware' => ['role:admin']], function () {
        //
    });

## Титры

Данный пакет вдохновлен и разработан на основе [jeremykenedy/laravel-roles](https://github.com/jeremykenedy/laravel-roles).

## Лицензия 

Этот пакет является бесплатным программным обеспечением, распространяемым на условиях [лицензии MIT](./LICENSE).