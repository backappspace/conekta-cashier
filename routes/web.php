<?php

Route::get('conekta/test', 'UvealSnow\ConektaCashier\WebhookController@test');
Route::post('conekta/webhook', 'UvealSnow\ConektaCashier\WebhookController@orderPaid');
