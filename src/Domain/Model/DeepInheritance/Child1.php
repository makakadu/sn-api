<?php
declare(strict_types=1);
namespace App\Domain\Model\DeepInheritance;

abstract class Child1 extends ParentClass {
    public string $name;
    public string $kek;
    public ?string $lol;
}
