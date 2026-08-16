<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Zenmanage\Laravel\Http\Controllers\WebhookController;

Route::post(
    (string) config('zenmanage.webhook.path', 'zenmanage/webhook'),
    WebhookController::class
)->name('zenmanage.webhook');
