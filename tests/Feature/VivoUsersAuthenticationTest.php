<?php

use App\Models\VivoUsers;
use App\Mail\RegistrationSuccessful;
use App\Mail\DebugTestEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

it('rejects invalid registration data', function () {
    $response = $this->from('/login/create')->post('/login/store', [
        'username' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'different',
    ]);

    $response->assertRedirect('/login/create');
    $response->assertSessionHasErrors([
        'username',
        'email',
        'password',
        'terms',
    ]);
});

it('registers a user with valid data', function () {
    Mail::fake();

    $response = $this->from('/login/create')->post('/login/store', [
        'username' => 'Victor',
        'email' => 'victor@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => 'on',
    ]);

    $response->assertRedirect(route('vivo_users.create'))
        ->assertSessionHas('status', 'Account created successfully. Please log in.');

    expect(VivoUsers::where('email', 'victor@gmail.com')->exists())->toBeTrue();
    expect(VivoUsers::where('email', 'victor@gmail.com')->first()->aquarium->sain)->toMatch('/^\d{20}$/');

    Mail::assertSent(RegistrationSuccessful::class, fn (RegistrationSuccessful $mail) => $mail->hasTo('victor@gmail.com'));
});

it('returns user json when registration explicitly requests json', function () {
    Mail::fake();

    $response = $this->postJson('/login/store', [
        'username' => 'Victor',
        'email' => 'victor@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => 'on',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'user created successfully...')
        ->assertJsonPath('user.email', 'victor@gmail.com')
        ->assertJsonStructure(['user' => ['aquarium' => ['sain']]]);

    Mail::assertSent(RegistrationSuccessful::class, fn (RegistrationSuccessful $mail) => $mail->hasTo('victor@gmail.com'));
});

it('sends a debug email to the authenticated user', function () {
    Mail::fake();
    $user = VivoUsers::create([
        'username' => 'Victor',
        'email' => 'victor@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user)
        ->get('/debug/mail')
        ->assertOk()
        ->assertJson(['message' => 'Test email sent.']);

    Mail::assertSent(DebugTestEmail::class, fn (DebugTestEmail $mail) => $mail->hasTo('victor@example.com'));
});

it('rejects incorrect login credentials', function () {
    VivoUsers::create([
        'username' => 'Victor',
        'email' => 'victor@gmail.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->from('/login/create')->post('/login', [
        'identifier' => 'victor@gmail.com',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login/create')
        ->assertSessionHasErrors(['identifier' => 'The provided credentials are incorrect.']);
});

it('returns incorrect login credentials as json when requested', function () {
    $response = $this->postJson('/login', [
        'identifier' => 'missing-user',
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['identifier']);
});

it('logs in with valid credentials', function () {
    VivoUsers::create([
        'username' => 'Victor',
        'email' => 'victor@gmail.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'identifier' => 'VICTOR@gmail.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'login successful')
        ->assertJsonStructure(['user' => ['aquarium' => ['sain']]]);
});

it('logs in with a username', function () {
    VivoUsers::create([
        'username' => 'Victor',
        'email' => 'victor@gmail.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'identifier' => 'Victor',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'login successful']);
});

it('logs out all sessions for the authenticated user only', function () {
    $user = VivoUsers::create([
        'username' => 'Victor',
        'email' => 'victor@gmail.com',
        'password' => Hash::make('password123'),
    ]);
    $user->setRememberToken('existing-remember-token');
    $user->save();

    $otherUser = VivoUsers::create([
        'username' => 'Other',
        'email' => 'other@gmail.com',
        'password' => Hash::make('password123'),
    ]);

    DB::table('sessions')->insert([
        [
            'id' => 'victor-device-one',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature Test',
            'payload' => '',
            'last_activity' => time(),
        ],
        [
            'id' => 'victor-device-two',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature Test',
            'payload' => '',
            'last_activity' => time(),
        ],
        [
            'id' => 'other-user-device',
            'user_id' => $otherUser->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature Test',
            'payload' => '',
            'last_activity' => time(),
        ],
    ]);

    $this->actingAs($user)
        ->post(route('vivo_users.logout_all'))
        ->assertRedirect(route('vivo_users.create'));

    $this->assertGuest();
    $this->assertDatabaseMissing('sessions', ['id' => 'victor-device-one']);
    $this->assertDatabaseMissing('sessions', ['id' => 'victor-device-two']);
    $this->assertDatabaseHas('sessions', ['id' => 'other-user-device']);
    $this->assertNotSame('existing-remember-token', $user->fresh()->getRememberToken());
});
