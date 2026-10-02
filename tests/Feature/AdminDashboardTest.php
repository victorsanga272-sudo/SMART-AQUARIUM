<?php

use App\Models\VivoUsers;
use Illuminate\Support\Facades\Hash;

function makeAdminPanelUser(string $email): VivoUsers
{
    return VivoUsers::create([
        'username' => 'panel-user',
        'email' => $email,
        'password' => Hash::make('password123'),
    ]);
}

test('admin dashboard requires an authenticated user', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('vivo_users.create'));
});

test('non-admin users cannot access the admin dashboard', function () {
    config()->set('app.admin_emails', ['admin@example.com']);

    $this->actingAs(makeAdminPanelUser('member@example.com'))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('allowlisted admins can see the monitoring dashboard', function () {
    config()->set('app.admin_emails', ['admin@example.com']);

    $this->actingAs(makeAdminPanelUser('admin@example.com'))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('System overview')
        ->assertSee('Aquarium accounts');
});