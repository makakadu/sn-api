<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

trait DescribableTrait {

    protected string $description = '';
    
    function description(): string {
        return $this->description;
    }
    
//    function changeDescription(string $description): void {
//        \Assert\Assertion::maxLength($description, 300);
//        $this->description = $description;
//    }

}
