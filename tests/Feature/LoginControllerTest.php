<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('logs in with valid credentials', function () {
    // Create a test user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Send POST request to the login route
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Assert that the response is OK and contains a token
    $response->assertStatus(200);
    $response->assertJsonStructure(['token', 'user']);
});


