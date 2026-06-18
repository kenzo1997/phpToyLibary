<?php
declare(strict_types=1);
namespace lib\middlewares;

use lib\http\Request;
use lib\http\Response;

/**
 * RateLimitMiddleware
 *
 * Rate limits requests based on IP address or user ID.
 * Uses file-based storage by default, can be extended for Redis/DB.
 */
class RateLimitMiddleware {
    private const DEFAULT_MAX_REQUESTS = 100;
    private const DEFAULT_WINDOW_SECONDS = 60;

    private int $maxRequests;
    private int $windowSeconds;
    private string $storagePath;

    public function __construct(
        int $maxRequests = self::DEFAULT_MAX_REQUESTS,
        int $windowSeconds = self::DEFAULT_WINDOW_SECONDS
    ) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->storagePath = __DIR__ . '/../storage/rate_limits/';
    }

    public function run(Request $request, Response $response, array $allowedRoles = []): void {
        $clientKey = $this->getClientKey($request);

        if (!$this->checkRateLimit($clientKey)) {
            $response->json([
                'status' => 'error',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->windowSeconds
            ], 429);
            exit;
        }
    }

    private function getClientKey(Request $request): string {
        // Try to get user ID from session first (from session key if set)
        if (isset($_SESSION) && session_status() === PHP_SESSION_ACTIVE) {
            $userId = $_SESSION['user'] ?? null;
            if ($userId) {
                return 'user:' . $userId;
            }
        }

        // Fall back to IP address
        return 'ip:' . $this->getClientIp($request);
    }

    private function getClientIp(Request $request): string {
        // Check for forwarded IP (behind proxy)
        if ($request->headers['X-Forwarded-For'] ?? false) {
            $ips = explode(',', $request->headers['X-Forwarded-For']);
            return trim($ips[0]);
        }

        return $request->headers['X-Real-IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function checkRateLimit(string $clientKey): bool {
        $this->ensureStorageDir();
        $rateLimitFile = $this->storagePath . md5($clientKey) . '.json';

        $currentTime = time();
        $data = $this->readRateLimitData($rateLimitFile);

        // Clean old entries outside the window
        $data = array_filter($data, fn($timestamp) => ($currentTime - $timestamp) < $this->windowSeconds);

        // Check if rate limit exceeded
        if (count($data) >= $this->maxRequests) {
            return false;
        }

        // Record this request
        $data[] = $currentTime;
        $this->writeRateLimitData($rateLimitFile, $data);

        return true;
    }

    private function ensureStorageDir(): void {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }
    }

    private function readRateLimitData(string $file): array {
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    private function writeRateLimitData(string $file, array $data): void {
        // Write to temp file first, then rename for atomicity
        $tempFile = $file . '.tmp';
        file_put_contents($tempFile, json_encode($data));
        rename($tempFile, $file);
    }
}
