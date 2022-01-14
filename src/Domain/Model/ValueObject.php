<?php
declare(strict_types=1);
namespace App\Domain\Model;

interface ValueObject {
    /**
     * Determine equality with another Value Object
     *
     * @param ValueObject $object
     * @return bool
     */
    function equals(ValueObject $object): bool;

    /** @return mixed */
    function value();
}
