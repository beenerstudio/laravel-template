<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_index()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get("/api/users?limit=10&offset=0");

        $response->assertStatus(200);
    }

    public function test_store_whenRequestInvalid_ReturnError()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->post("/api/users", [
            "name" => "isjhar",
        ]);

        $response->assertStatus(400);
    }

    public function test_store_whenRequestValid_ReturnSuccess()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->post("/api/users", [
            "name" => "test",
            "email" => "test@gmail.com",
            "password" => "test",
            "roles" => [1],
        ]);

        $response->assertStatus(200);
    }

    public function test_update_whenRequestValid_ReturnSuccess()
    {
        $user = User::find(1);

        $updatedRow = User::orderBy("id", "desc")->first();

        $response = $this->actingAs($user)->patch(
            sprintf("/api/users/%d", $updatedRow->id),
            [
                "name" => "test",
                "email" => "test@gmail.com",
                "password" => "test",
                "roles" => [2],
            ]
        );

        $response->assertStatus(200);
    }

    public function test_update_whenUserChangeRoleAsSysAdmin_ReturnError()
    {
        $user = User::find(1);

        $updatedRow = User::orderBy("id", "desc")->first();

        $response = $this->actingAs($user)->patch(
            sprintf("/api/users/%d", $updatedRow->id),
            [
                "name" => "test",
                "email" => "test@gmail.com",
                "password" => "test",
                "roles" => [1],
            ]
        );

        $response->assertStatus(400);
    }

    public function test_update_whenUserIsSysAdmin_ReturnError()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->patch("/api/users/1", [
            "name" => "test",
            "email" => "test@gmail.com",
            "password" => "test",
            "roles" => [1],
        ]);

        $response->assertStatus(400);
    }

    public function test_update_whenUserNotFound_ReturnError()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->patch("/api/users/998", [
            "name" => "test",
            "email" => "test@gmail.com",
            "password" => "test",
            "roles" => [1],
        ]);

        $response->assertStatus(400);
    }

    public function test_delete_whenUserExist_ReturnSuccess()
    {
        $user = User::find(1);

        $deletedRow = User::orderBy("id", "desc")->first();

        $response = $this->actingAs($user)->delete(
            sprintf("/api/users/%d", $deletedRow->id)
        );

        $response->assertStatus(200);
    }
}
