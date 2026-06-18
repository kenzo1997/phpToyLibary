<?php
declare(strict_types=1);
namespace lib\exception;

use Exception;
use Throwable;
use lib\http\Request;
use lib\http\Response;

class ErrorHandler {
    private bool $debugMode;
    private ?string $logPath;

    public function __construct(bool $debugMode = false, ?string $logPath = null) {
        $this->debugMode = $debugMode;
        $this->logPath = $logPath ?: __DIR__ . '/../../storage/logs/app.log';
    }

    public function register(): void {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $level, string $message, ?string $file = null, ?int $line = null): bool {
        if (error_reporting() & $level) {
            $this->logError($message, $file, $line, $level);
            if ($this->debugMode) {
                echo "<b>Error [$level]</b>: {$message} in {$file}:{$line}<br>";
                return true;
            }
        }
        return false;
    }

    public function handleException(Throwable $exception): void {
        $this->logException($exception);

        http_response_code($this->getStatusCode($exception));

        if ($this->debugMode) {
            $this->showDebugMode($exception);
        } else {
            $this->showProductionMode($exception);
        }

        exit(1);
    }

    public function handleShutdown(): void {
        $error = error_get_last();
        if ($error !== null) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    private function logError(string $message, ?string $file, ?int $line, int $level): void {
        $levelNames = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        $levelName = $levelNames[$level] ?? 'UNKNOWN';
        $this->writeLog("[{$levelName}] {$message} in {$file}:{$line}");
    }

    private function logException(Throwable $exception): void {
        $trace = $exception->getTrace();
        $traceString = [];
        foreach ($trace as $frame) {
            $file = $frame['file'] ?? 'unknown';
            $line = $frame['line'] ?? 'unknown';
            $traceString[] = "{$file}:{$line}";
        }

        $log = sprintf(
            "[%s] %s: %s\nFile: %s\nLine: %d\nTrace: %s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            implode(' -> ', $traceString)
        );

        $this->writeLog($log);
    }

    private function writeLog(string $message): void {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->logPath, $message, FILE_APPEND);
    }

    private function getStatusCode(Throwable $exception): int {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return match(get_class($exception)) {
            'PDOException' => 500,
            'InvalidArgumentException' => 400,
            'RuntimeException' => 500,
            default => 500,
        };
    }

    private function showDebugMode(Throwable $exception): void {
        echo "<h1>" . get_class($exception) . "</h1>";
        echo "<p><b>Message:</b> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><b>File:</b> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><b>Line:</b> " . $exception->getLine() . "</p>";

        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";

        if ($exception->getPrevious()) {
            echo "<h2>Previous Exception:</h2>";
            $this->showDebugMode($exception->getPrevious());
        }
    }

    private function showProductionMode(Throwable $exception): void {
        echo "<h1>Something went wrong</h1>";
        echo "<p>We're working to fix the problem. Please try again later.</p>";
    }
}
