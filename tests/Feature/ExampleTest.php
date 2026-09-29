<?php

test('the root URL redirects to the sign in page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('vivo_users.create'));
});
