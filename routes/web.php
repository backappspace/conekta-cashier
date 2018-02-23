<?php

Route::post('conekta/webhook', 'UvealSnow\ConektaCashier\Controllers\WebhookController@orderPaid');

// test, please keep it as a comment
// Route::get('test/subscription', 'UvealSnow\ConektaCashier\Controllers\WebhookController@test');
