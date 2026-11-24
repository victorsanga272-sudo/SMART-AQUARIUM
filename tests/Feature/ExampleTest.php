<?php

test('the root URL redirects to the sign in page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('vivo_users.create'));
});

test('the landing page login link opens the sign in page', function () {
    $this->get('/index')
        ->assertOk()
        ->assertSee('href="' . route('vivo_users.create') . '"', false);

    $this->get(route('vivo_users.create'))->assertOk();
});

test('the sign in page references its public background image', function () {
    $this->get(route('vivo_users.create'))
        ->assertOk()
        ->assertSee(asset('images/4.jpg'));
});
