<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a user with valid data', function () {
$response = $this->postJson('/api/register', [
'name' => 'John',
'last_name' => 'Doe',
'email' => 'john.doe@example.com',
'password' => 'password123',
]);

$response->assertStatus(201);
$response->assertJson(['message' => 'User registered successfully']);

$this->assertDatabaseHas('users', [
'email' => 'john.doe@example.com'
]);
});

it('does not register a user with missing required fields', function () {
$response = $this->postJson('/api/register', [
'name' => 'John',
'email' => 'john.doe@example.com',
]);

$response->assertStatus(422);
$response->assertJsonStructure(['message', 'errors' => ['last_name', 'password']]);
});

it('does not register a user with a duplicate email', function () {
// Create a user to simulate duplicate email scenario
User::factory()->create([
'email' => 'duplicate@example.com'
]);

$response = $this->postJson('/api/register', [
'name' => 'Jane',
'last_name' => 'Doe',
'email' => 'duplicate@example.com',
'password' => 'password123',
]);

$response->assertStatus(422);
$response->assertJsonStructure(['message', 'errors' => ['email']]);
});
