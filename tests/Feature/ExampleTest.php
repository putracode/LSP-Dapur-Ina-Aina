<?php

test('the root redirects guest to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
