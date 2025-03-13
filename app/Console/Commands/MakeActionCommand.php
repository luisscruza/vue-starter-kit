<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final class MakeActionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new action';

    /**
     * Create a new command instance.
     */
    public function __construct(/**
     * The filesystem instance.
     */
        private readonly Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        // Append "Action" suffix to the name if it doesn't already have it
        if (! Str::endsWith($name, 'Action')) {
            $name .= 'Action';
        }

        $path = $this->getPath($name);

        if ($this->files->exists($path)) {
            $this->fail("Action {$name} already exists.");
        }

        // Create the directory if it doesn't exist
        $this->makeDirectory($path);

        // Generate the action file
        $this->files->put($path, $this->buildClass($name));

        $this->info("Action {$name} created successfully.");
    }

    /**
     * Build the class with the given name.
     */
    private function buildClass(string $name): string
    {
        $stub = $this->files->get($this->getStubPath());

        $class = Str::studly(class_basename($name));

        // For nested directories, we need to get the full namespace
        $namespace = str_contains($name, '/') ? 'App\\Actions\\'.str_replace('/', '\\', dirname($name)) : 'App\\Actions';

        return str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $class],
            $stub
        );
    }

    /**
     * Get the stub file for the generator.
     */
    private function getStubPath(): string
    {
        return base_path('stubs/action.stub');
    }

    /**
     * Get the full path to the action file.
     */
    private function getPath(string $name): string
    {
        $name = Str::replaceFirst('App\\', '', $name);

        return app_path("Actions/{$name}.php");
    }

    /**
     * Create the directory for the action if it doesn't exist.
     */
    private function makeDirectory(string $path): string
    {
        $directory = dirname($path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        return $directory;
    }
}
