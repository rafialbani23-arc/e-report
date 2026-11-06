<?php

test('the application directs visitors to sign in', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
