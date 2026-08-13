<?php

declare(strict_types=1);

use Crmleaf\Payroll\Tools\GratuityCalculator\Http\Controllers\GratuityCalculatorController;
use Illuminate\Support\Facades\Route;

/*
 * Loaded by GratuityCalculatorServiceProvider only when config('gratuity-calculator.route.enabled')
 * is true, so requiring the package never adds a URL on its own.
 */

/** @var \Illuminate\Contracts\Config\Repository $config */
$config = app('config');

Route::middleware((array) $config->get('gratuity-calculator.route.middleware', ['web']))
    ->prefix((string) $config->get('gratuity-calculator.route.prefix', 'tools'))
    ->group(static function () use ($config): void {
        Route::match(['get', 'post'], '/gratuity-calculator', GratuityCalculatorController::class)
            ->name((string) $config->get('gratuity-calculator.route.name', 'gratuity-calculator'));
    });
