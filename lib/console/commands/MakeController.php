<?php

namespace lib\console\commands;

class MakeController
{
    public function __invoke(array $args): void
    {
        $name = $args[0] ?? null;

        if (!$name) {
            echo "Usage: make:controller ControllerName\n";
            return;
        }

        $templatePath = __DIR__ . '/../templates/controller.template.php';

        if (!file_exists($templatePath)) {
            echo "Template not found: $templatePath\n";
            return;
        }

        $content = file_get_contents($templatePath);
        $content = str_replace('{{namespace}}', 'controller', $content);
        $content = str_replace('{{name}}', $name, $content);
        $content = str_replace('{{CREATION_DATE}}', date('Y-m-d H:i:s'), $content);

        // Use current working directory as output dir
        $outputDir = getcwd();

        file_put_contents("{$outputDir}/{$name}Controller.php", $content);
        echo "Controller '{$name}Controller' created successfully.\n";
    }

    public function description(): string
    {
        return 'Generate a new controller class.';
    }
}

