<?php
declare(strict_types=1);
namespace lib\http;

class Response {
  private array $headers = [];
  private mixed $body;

  public function setHeader(string $name, string $value): void {
    $this->headers[$name] = $value;
  }

  public function send(string $message): void {
    $this->sendHeaders();
    echo $message;
  }

  public function json(mixed $data, int $status = 200): void {
    $this->setHeader('Content-Type', 'application/json');
    http_response_code($status);
    $this->sendHeaders();
    echo json_encode($data);
  }

  public function xml(array $data): void {
    $this->setHeader('Content-Type', 'application/xml');
    $this->sendHeaders();

    // Simple XML conversion (recursive)
    $xml = new \SimpleXMLElement('<response/>');
    $this->arrayToXml($data, $xml);
    echo $xml->asXML();
  }

  public function stream(callable $generator): void {
    $this->setHeader('Content-Type', 'text/plain');
    $this->sendHeaders();
    while ($chunk = $generator()) {
      echo $chunk;
      flush();
    }
  }

  public function redirect(string $url, int $statusCode = 302): void {
    //echo $url;
    //header('Location: '.$url);
    //http_response_code($statusCode);
    $this->setHeader('Location', $url);
    $this->sendHeaders();
    exit;
  }

  private function sendHeaders(): void {
    foreach ($this->headers as $name => $value) {
      header("{$name}: {$value}");
    }
  }

  private function arrayToXml(array $data, \SimpleXMLElement $xml): void {
    foreach ($data as $key => $value) {
      is_array($value)
        ? $this->arrayToXml($value, $xml->addChild($key))
        : $xml->addChild($key, htmlspecialchars((string)$value));
    }
  }

  public function success(mixed $data = null, string $message = '', int $status = 200): void {
    $response = ['status' => 'success', 'data' => $data, 'message' => $message];
    $this->json($response, $status);
  }

  public function error(string $message = '', int $status = 400, mixed $data = null): void {
    $response = ['status' => 'error', 'message' => $message, 'data' => $data];
    $this->json($response, $status);
  }

  public function notFound(string $message = 'Resource not found'): void {
    $this->error($message, 404);
  }

  public function unauthorized(string $message = 'Unauthorized'): void {
    $this->error($message, 401);
  }

  public function forbidden(string $message = 'Forbidden'): void {
    $this->error($message, 403);
  }

  public function internalError(string $message = 'Internal server error'): void {
    $this->error($message, 500);
  }
}
