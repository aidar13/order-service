<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Tests\TestCase;

/** Guards the wiring: a new command without a mapped handler fails the build. */
final class CommandBusTest extends TestCase
{
    public function test_every_command_is_mapped_to_its_handler(): void
    {
        $files = File::allFiles(app_path('Commands'));
        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            /** @var class-string $class */
            $class   = 'App\\Commands\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $handler = app(Dispatcher::class)->getCommandHandler((new ReflectionClass($class))->newInstanceWithoutConstructor());

            $expected = Str::of($class)->replace('App\\Commands\\', 'App\\Handlers\\')->replaceLast('Command', 'Handler')->toString();
            $this->assertIsObject($handler, "{$class} has no handler");
            $this->assertSame($expected, $handler::class);
        }
    }
}
