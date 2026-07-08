<?php

use App\Models\Setting;

it('returns a successful response', function () {
    Setting::setSetupComplete(true);

    $response = $this->get('/');

    $response->assertRedirect('/login');
});
