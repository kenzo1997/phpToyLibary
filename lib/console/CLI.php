<?php
namespace lib\console;

use lib\console\output\ConsoleOutput;

class CLI
{
    protected array $commands = [];

    public function __construct()
    {
        $this->register('make:controller', commands\MakeController::class);
        $this->register('make:middleware', commands\MakeMiddleware::class);
        $this->register('make:model', commands\MakeModel::class);
        // Add more as needed
    }

    public function register(string $name, string $class): void
    { 
        $this->commands[$name] = $class;
    }

    public function handle(array $argv): void
    {
        $commandName = $argv[1] ?? 'list';

        if (in_array($commandName, ['list', '--help', '-h'])) {
            $this->listCommands();
            return;
        }

        if (!isset($this->commands[$commandName])) {
            ConsoleOutput::write("Command not found: {$commandName}", 'red');
            $this->listCommands();
            return;
        }

        $args = array_slice($argv, 2);
        $handler = new ($this->commands[$commandName]);

        if (!is_callable($handler)) {
            ConsoleOutput::write("Command handler for {$commandName} is not callable.", 'red');
            return;
        }

        $handler($args);
    }

    protected function listCommands(): void
    {
        ConsoleOutput::write("Available commands:", 'blue');
        foreach ($this->commands as $name => $class) {
            $desc = method_exists($class, 'description') ? (new $class())->description() : '';
            echo "  - {$name}" . ($desc ? " — {$desc}" : "") . PHP_EOL;
        }
    }
}
?>
