<?php

namespace lib\console\commands;

class MakeMiddleware
{
    public function __invoke(array $args): void
    {
        $name = $args[0] ?? null;

        if (!$name) {
            echo "Usage: make:middleware MiddlewareName\n";
            return;
        }

        $templatePath = __DIR__ . '/../Templates/middleware.template.php';

        if (!file_exists($templatePath)) {
            echo "Template not found: $templatePath\n";
            return;
        }

        $content = file_get_contents($templatePath);
        $content = str_replace('{{namespace}}', 'middleware', $content);
        $content = str_replace('{{name}}', $name, $content);
        $content = str_replace('{{CREATION_DATE}}', date('Y-m-d H:i:s'), $content);

        $outputDir = getcwd();
        $filePath = "{$outputDir}/{$name}.php";
        file_put_contents($filePath, $content);

        echo "Middleware '{$name}' created successfully at {$filePath}.\n";
    }

    public function description(): string
    {
        return 'Generate a new middleware class.';
    }
}

