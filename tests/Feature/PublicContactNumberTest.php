<?php

test('public pages show updated whatsapp contact number', function (string $url) {
    $this->get($url)
        ->assertSuccessful()
        ->assertSee('+62 838-3946-3566', false);
})->with([
    '/privacy-policy',
    '/terms-of-service',
    '/account-deletion',
    '/support',
]);

test('support page uses updated whatsapp deeplink', function () {
    $this->get('/support')
        ->assertSuccessful()
        ->assertSee('https://wa.me/6283839463566', false);
});
