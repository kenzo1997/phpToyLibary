<?php
declare(strict_types=1);
namespace lib\http;

class Request {
  private array $params = [];
  public array $query;
  public array $body;
  public array $files;
  public array $headers;
  public string $method;

  public function __construct() {
    $this->query = $_GET ?? [];
    $this->files = $_FILES ?? [];
    $this->headers = getallheaders() ?: [];
    $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Parse body
    $raw = file_get_contents('php://input');
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
      $this->body = json_decode($raw, true) ?? [];
    } else {
      $this->body = $_POST ?? [];
    }
  }

  public function setParam(string $name, mixed $value): void {
    if (empty($name)) {
      throw new \Exception('Param name cannot be empty');
    }
    $this->params[$name] = $value;
  }

  public function getParam(string $name): mixed {
    var_dump($this->params);

    if (!array_key_exists($name, $this->params)) {
      throw new \Exception("Param key does not exist: {$name}");
    }
    return $this->params[$name];
  }

  public function getParams(): array {
    return $this->params;
  }

  public function input(string $key, mixed $default = null): mixed {
    return $this->body[$key] ?? $this->query[$key] ?? $default;
  }

  public function has(string $key): bool {
    return isset($this->body[$key]) || isset($this->query[$key]);
  }

  public function file(string $key): ?array {
    return $this->files[$key] ?? null;
  }
}

?>
