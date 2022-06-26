<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TokenTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_whenInvalidCredential_ReturnError()
    {
        $response = $this->post("/api/tokens/create", [
            "email" => "sysadmin@gmail.com",
            "password" => "1",
        ]);

        $response->assertStatus(400);
    }

    public function test_whenInvalidCredential_ReturnSuccess()
    {
        $response = $this->post("/api/tokens/create", [
            "email" => "sysadmin@gmail.com",
            "password" => "1234",
        ]);

        $response->assertStatus(200);
    }
}
