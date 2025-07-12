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
    http_response_code($statusCode);
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
}
?>
