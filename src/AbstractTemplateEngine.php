<?php

declare(strict_types=1);

namespace App;

abstract class AbstractTemplateEngine {
    abstract public function render(string $templateName, array $data);
}
