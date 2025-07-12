<?php
//namespace lib\view;
namespace lib\template;

class TemplateEngine {
    public function render(string $template, array $data = []): string {
        $viewPath = __DIR__ . '/../../app/views/' . str_replace('.', '/', $template) . '.blade.php';
        // $viewPath = __DIR__ . '/../app/views/' . str_replace('.', '/', $template) . '.blade.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found at: $viewPath");
        }

        // Compile the template content to PHP code
        $compiled = $this->compile(file_get_contents($viewPath));

        // Extract data into variables for use in the template
        extract($data);

        // Start output buffering and include the compiled content
        ob_start();
        include $this->getCompiledTemplatePath($compiled);
        return ob_get_clean();
    }

    private function compile(string $content): string {
        // Convert Blade-like syntax to PHP code
        // Echo raw (no escaping)
        $content = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?php echo $1; ?>', $content);

        // Echo escaped
        $content = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?php echo htmlspecialchars($1); ?>', $content);
      
    
        // If statements (fixed regex)
        $content = preg_replace('/@if\s*\(((?:[^()]+|\([^()]*\))*)\)/', '<?php if ($1): ?>', $content);
        $content = preg_replace('/@elseif\s*\((.+?)\)/', '<?php elseif ($1): ?>', $content);
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);

        
        // Foreach loops
        $content = preg_replace('/@foreach\s*\((.+?)\)/', '<?php foreach ($1): ?>', $content);
        $content = preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);

        // Includes (basic version)
        $content = preg_replace_callback('/@include\s*\(\s*[\'"](.+?)[\'"]\s*\)/', function ($matches) {
            $includedPath = __DIR__ . '/../../app/views/' . str_replace('.', '/', $matches[1]) . '.blade.php';
            // $includedPath = __DIR__ . '/../app/views/' . str_replace('.', '/', $matches[1]) . '.blade.php';
            return file_exists($includedPath) ? file_get_contents($includedPath) : '';
        }, $content);

        return $content;
    }

    private function getCompiledTemplatePath(string $compiledContent): string {
        // For simplicity, we'll write the compiled content to a temporary file
        $tempDir = __DIR__ . '../../storage/cache/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Use a hash to generate a unique file name
        $compiledFile = $tempDir . md5($compiledContent) . '.php';

        // Write the compiled content to the file if it doesn't already exist
        if (!file_exists($compiledFile)) {
            file_put_contents($compiledFile, $compiledContent);
        }

        return $compiledFile;
    }

    public function renderPhp(string $template, array $data = []): string {
        $viewPath = __DIR__ . '/../../app/views/' . str_replace('.', '/', $template) . '.php';
        // $viewPath = __DIR__ . '/../app/views/' . str_replace('.', '/', $template) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: $viewPath");
        }

        extract($data);
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}
?>
