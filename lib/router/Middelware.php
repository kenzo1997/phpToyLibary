<?php
namespace lib\router;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Middelware {
    public string $middlewareClass;
    public array $allowedRoles;

     public function __construct(string $middlewareClass, array $allowedRoles = []) {
        $this->middlewareClass = $middlewareClass;
        $this->allowedRoles = $allowedRoles;
    }
}
?>
