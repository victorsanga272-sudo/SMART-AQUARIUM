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

test('non-admin users cannot delete accounts', function () {
    config()->set('app.admin_emails', ['admin@example.com']);
    $member = makeAdminPanelUser('member@example.com');
    $target = makeAdminPanelUser('target@example.com');

    $this->actingAs($member)
        ->delete(route('admin.users.destroy', $target))
        ->assertForbidden();

    $this->assertDatabaseHas('vivo_users', ['id' => $target->id]);
});

test('allowlisted admins can see the monitoring dashboard', function () {
    config()->set('app.admin_emails', ['admin@example.com']);

    $this->actingAs(makeAdminPanelUser('admin@example.com'))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('System overview')
        ->assertSee('Aquarium accounts')
        ->assertSee(route('vivo_users.logout'), false)
        ->assertSee('Log out')
        ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private')
        ->assertHeader('Pragma', 'no-cache');
});

test('admins can delete other accounts and their aquarium data', function () {
    config()->set('app.admin_emails', ['admin@example.com']);
    $admin = makeAdminPanelUser('admin@example.com');
    $user = makeAdminPanelUser('member@example.com');
    $aquarium = $user->aquarium;
    $telemetry = $aquarium->telemetry()->create([
        'temperature' => 24.5,
        'ph' => 7.2,
        'turbidity' => 1.1,
    ]);
    $command = $aquarium->actuatorCommands()->create([
        'command' => 'light-on',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.users.destroy', $user), false);

    $this->from(route('admin.dashboard'))
        ->actingAs($admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('vivo_users', ['id' => $user->id]);
    $this->assertDatabaseMissing('aquariums', ['id' => $aquarium->id]);
    $this->assertDatabaseMissing('aquarium_telemetry', ['id' => $telemetry->id]);
    $this->assertDatabaseMissing('aquarium_actuator_commands', ['id' => $command->id]);
});

test('admins must use self-service deletion for their own account', function () {
    config()->set('app.admin_emails', ['admin@example.com']);
    $admin = makeAdminPanelUser('admin@example.com');

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertForbidden();

    $this->assertDatabaseHas('vivo_users', ['id' => $admin->id]);
});

test('admins can log out of their current session', function () {
    config()->set('app.admin_emails', ['admin@example.com']);
    $admin = makeAdminPanelUser('admin@example.com');

    $this->actingAs($admin)
        ->post(route('vivo_users.logout'))
        ->assertRedirect(route('vivo_users.create'));

    $this->assertGuest();

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('vivo_users.create'));
});