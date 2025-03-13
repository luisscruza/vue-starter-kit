<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any previously generated test actions
    if (File::exists(app_path('Actions/Test'))) {
        File::deleteDirectory(app_path('Actions/Test'));
    }
});

afterEach(function () {
    // delete both files...
    if (File::exists(app_path('Actions/Test'))) {
        File::deleteDirectory(app_path('Actions/Test'));
    }
    // Check for the file and delete it if it exists
    if (File::exists(app_path('Actions/TestAction.php'))) {
        File::delete(app_path('Actions/TestAction.php'));
    }
});

it('can create a basic action file', function () {
    $actionName = 'Test/TestAction';
    $expectedPath = app_path('Actions/Test/TestAction.php');

    $this->artisan('make:action', ['name' => $actionName])
        ->assertSuccessful()
        ->assertExitCode(0);

    expect(File::exists($expectedPath))->toBeTrue();

    $generatedFile = File::get($expectedPath);
    expect($generatedFile)
        ->toContain('namespace App\Actions\Test')
        ->toContain('final class TestAction')
        ->toContain('public function handle(array $attributes)')
        ->toContain('declare(strict_types=1)');
});

it('properly formats the namespace for actions without nesting', function () {

    $actionName = 'TestAction';
    $expectedPath = app_path('Actions/TestAction.php');

    $this->artisan('make:action', ['name' => $actionName])
        ->assertSuccessful();

    expect(File::exists($expectedPath))->toBeTrue();

    $generatedFile = File::get($expectedPath);
    expect($generatedFile)
        ->toContain('namespace App\Actions')
        ->toContain('final class TestAction');
});

it('can create a nested action file', function () {

    $actionName = 'Test/Nested/TestAction';
    $expectedPath = app_path('Actions/Test/Nested/TestAction.php');

    $this->artisan('make:action', ['name' => $actionName])
        ->assertSuccessful();

    expect(File::exists($expectedPath))->toBeTrue();

    $generatedFile = File::get($expectedPath);
    expect($generatedFile)
        ->toContain('namespace App\Actions\Test\Nested')
        ->toContain('final class TestAction');
});

it('appends the action suffix to the action name if it is not provided', function () {
    $actionName = 'Test';
    $expectedPath = app_path('Actions/TestAction.php');

    $this->artisan('make:action', ['name' => $actionName])
        ->assertSuccessful();

    expect(File::exists($expectedPath))->toBeTrue();

    $generatedFile = File::get($expectedPath);
    expect($generatedFile)
        ->toContain('namespace App\Actions')
        ->toContain('final class TestAction');

});

it('throws an error if the action already exists', function () {
    $actionName = 'TestAction';
    $expectedPath = app_path('Actions/TestAction.php');

    $this->artisan('make:action', ['name' => $actionName])
        ->assertSuccessful();

    expect(File::exists($expectedPath))->toBeTrue();

    $this->artisan('make:action', ['name' => $actionName])
        ->expectsOutputToContain('Action already exists')
        ->assertFailed();
});
