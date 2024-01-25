<?php

namespace dmitryrogolev\Is\Tests\Feature\Http\Controllers;

use dmitryrogolev\Is\Facades\Is;
use dmitryrogolev\Is\Tests\RefreshDatabase;
use dmitryrogolev\Is\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\AssertableJson;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected string $user;

    protected string $slugName;

    public function setUp(): void
    {
        parent::setUp();

        config(['is.uses.api' => true]);
        $this->seed(config('is.seeders.role'));
        $this->user = config('is.models.user');
        $this->slugName = app(config('is.models.role'))->getSlugName();
    }

    public function test_redirect(): void
    {
        $response = $this->get(route('roles.index'));
        $response->assertRedirectToRoute('login');

        $user = $this->generate($this->user);
        $this->actingAs($user);
        $response = $this->get(route('roles.index'));
        $response->assertRedirectToRoute('welcome');
    }

    public function test_index(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->get(route('roles.index'));
        $response->assertStatus(200);

        $count = Is::index()->count();
        $role = Is::index()->first();

        $response
            ->assertJson(fn (AssertableJson $json) => $json->has('data', $count, fn (AssertableJson $json) => $json->where('id', $role->id)
                ->where('name', $role->name)
                ->where($role->getSlugName(), $role->getSlug())
                ->where('description', $role->description)
                ->where('level', $role->level)
                ->where('createdAt', $role->created_at->toJSON())
                ->where('updatedAt', $role->updated_at->toJSON())
                ->etc()
            )
            );
    }

    public function test_show(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $role = $user->role();

        $response = $this->get(route('roles.show', ['role' => $role->id]));
        $response->assertStatus(200);

        $response
            ->assertJson(fn (AssertableJson $json) => $json->has('data', fn (AssertableJson $json) => $json->where('id', $role->id)
                ->where('name', $role->name)
                ->where($role->getSlugName(), $role->getSlug())
                ->where('description', $role->description)
                ->where('level', $role->level)
                ->where('createdAt', $role->created_at->toJSON())
                ->where('updatedAt', $role->updated_at->toJSON())
                ->etc()
            )
            );
    }

    public function test_store(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $attributes = [
            'name' => 'Moderator',
            'slug' => 'moderator',
            'description' => 'Moderator role',
            'level' => 2,
        ];

        $response = $this->post(route('roles.store'), $attributes);
        $response->assertStatus(201);

        $response
            ->assertJson(fn (AssertableJson $json) => $json->has('data', fn (AssertableJson $json) => $json->where('name', $attributes['name'])
                ->where($this->slugName, $attributes[$this->slugName])
                ->where('description', $attributes['description'])
                ->where('level', $attributes['level'])
                ->etc()
            )
            );

        $role = Is::find(json_decode($response->content())->data->id);

        $this->assertModelExists($role);
    }

    public function test_update(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $role = Is::generate();
        $attributes = [
            'name' => 'Moderator',
            'slug' => 'moderator',
            'description' => 'Moderator role',
            'level' => 2,
        ];

        $response = $this->patch(route('roles.update', ['role' => $role->id]), $attributes);
        $response->assertStatus(200);

        $response
            ->assertJson(fn (AssertableJson $json) => $json->has('data', fn (AssertableJson $json) => $json->where('id', $role->id)
                ->where('name', $attributes['name'])
                ->where($this->slugName, $attributes[$this->slugName])
                ->where('description', $attributes['description'])
                ->where('level', $attributes['level'])
                ->etc()
            )
            );
    }

    public function test_destroy(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        if (config('is.uses.soft_deletes')) {
            $role = Is::generate();

            $response = $this->delete(route('roles.destroy', ['role' => $role->id]));
            $response->assertStatus(200);

            $this->assertSoftDeleted($role);
        } else {
            $role = Is::generate();

            $response = $this->delete(route('roles.destroy', ['role' => $role->id]));
            $response->assertNoContent();

            $this->assertModelMissing($role);
        }
    }

    public function test_restore(): void
    {
        if (! config('is.uses.soft_deletes')) {
            $this->markTestSkipped();
        }

        $user = $this->createUser();
        $this->actingAs($user);

        $role = Is::generate();
        $role->delete();
        $this->assertSoftDeleted($role);

        $response = $this->patch(route('roles.restore', ['role' => $role->id]));
        $response->assertStatus(200);

        $this->assertNotSoftDeleted($role);
    }

    public function test_force_destroy(): void
    {
        if (! config('is.uses.soft_deletes')) {
            $this->markTestSkipped();
        }

        $user = $this->createUser();
        $this->actingAs($user);

        $role = Is::generate();
        $role->delete();
        $this->assertSoftDeleted($role);

        $response = $this->delete(route('roles.forceDestroy', ['role' => $role->id]));
        $response->assertNoContent();

        $this->assertModelMissing($role);
    }

    protected function createUser(): Model
    {
        $user = $this->generate($this->user);
        $role = Is::firstWhereUniqueKey('admin');
        $user->attachRole($role);

        return $user;
    }
}
