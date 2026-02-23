<?php
namespace lib\console\commands;

class MakeModel
{
    public function __invoke(array $args): void
    {
        $name = $args[0] ?? null;
        if (!$name) {
            echo "Usage: make:model ModelName [property:type ...]\n";
            return;
        }

        $properties = array_slice($args, 1);

        // Parse properties into promoted constructor parameters
        // e.g. public string $title, public int $age
        $constructorParams = '';

        foreach ($properties as $prop) {
            [$propName, $type] = explode(':', $prop) + [null, 'mixed'];
            $constructorParams .= "public {$type} \${$propName}, ";
        }

        $constructorParams = rtrim($constructorParams, ', ');

        // Load template
        $templatePath = __DIR__ . '/../templates/model.template.php';
        if (!file_exists($templatePath)) {
            echo "Template not found: $templatePath\n";
            return;
        }

        $content = file_get_contents($templatePath);
        $content = str_replace('{{namespace}}', 'app\models', $content);
        $content = str_replace('{{name}}', $name, $content);
        $content = str_replace('{{CREATION_DATE}}', date('Y-m-d H:i:s'), $content);
        $content = str_replace('{{constructor_params}}', $constructorParams, $content);

        $outputDir = getcwd();
        $filePath = "{$outputDir}/{$name}.php";
        file_put_contents($filePath, $content);

        echo "Model '{$name}' created at {$filePath}.\n";
    }

    public function description(): string
    {
        return 'Generate a new model class using constructor property promotion.';
    }
}
?>

