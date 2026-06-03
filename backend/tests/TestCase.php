<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    protected function apiToken(string $email): string
    {
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        $response->assertOk();

        return $response->json('token');
    }

    protected function asBearer(string $token): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$token);
    }
}
