<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\Factory;

arch()->preset()->php();

arch()->preset()->security();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('avoid inheritance')
    ->expect('App')
    ->classes()
    ->toExtendNothing()
    ->ignoring([
        'App\Models',
        'App\Exceptions',
        'App\Jobs',
        'App\Providers',
        'App\Services',
        'App\Http\Middleware',
        'App\Http\Requests',
        'App\Console\Commands',
        'App\Notifications',
    ]);

arch('annotations')
    ->expect('App')
    ->toHavePropertiesDocumented()
    ->toHaveMethodsDocumented();

arch('avoid open for extension')
    ->expect('App')
    ->classes()
    ->toBeFinal();

arch('avoid abstraction')
    ->expect('App')
    ->not->toBeAbstract();

arch('factories')
    ->expect('Database\Factories')
    ->toExtend(Factory::class)
    ->toHaveMethod('definition')
    ->toOnlyBeUsedIn([
        'App\Models',
    ]);

arch('models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toOnlyBeUsedIn([
        'App\Http',
        'App\Jobs',
        'App\Models',
        'App\Traits',
        'App\Providers',
        'App\Actions',
        'App\Policies',
        'App\Services',
        'Database\Factories',
        'Database\Seeders',
        'App\Notifications',
    ]);

arch('actions')
    ->expect('App\Actions')
    ->toHaveMethod('handle');
