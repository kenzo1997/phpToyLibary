<?php
declare(strict_types=1);
namespace lib\controller;

use lib\template\TemplateEngine;

/**
 * Controller
 *
 * @package  controller
 * @author   Kenzo Coenaerts
 */
abstract class Controller {
    protected static string $viewBasePath = __DIR__ . '/../../app/views/';
    // protected string $layout = 'layouts.main';
    protected string $layout = '';
    protected TemplateEngine $templateEngine;

    public function __construct() {
    }

    /**
     * Set a custom base path for view files
     */
    public static function setViewBasePath(string $path): void {
        self::$viewBasePath = rtrim($path, '/') . '/';
    }

    /**
     * Render a view using dot notation (e.g., "home.index")
     */
    protected function render(string $view, array $data = []): string {
        $this->templateEngine = new TemplateEngine();
        
        // Render the main view first
        $content = $this->templateEngine->render($view, $data);

        // Inject it into the layout (pass $content as data)
        if ($this->layout) {
            return $this->templateEngine->render($this->layout, ['content' => $content]);
        }

        return $content;
    }

    protected function setLayout(string $layout): void {
        $this->layout = $layout;
    }

    protected function renderPartial(string $view, array $data = []): void {
        //$viewPath = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';
        $viewPath = self::$viewBasePath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("Partial view not found: $viewPath");
        }

        extract($data);
        include $viewPath;
    }
}
?>

