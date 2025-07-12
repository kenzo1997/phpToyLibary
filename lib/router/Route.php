<?php
namespace lib\router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Route {
    public function __construct(
        public ?string $path = null,
        public array $methods = ['GET'],
        public ?string $name = null,
        public array $requirements = [], // e.g. ['id' => '\d+']
        public ?string $domain = null,
        public ?string $prefix = null, // for class-level
        public array $defaults = []
    ) {}
}
?>

