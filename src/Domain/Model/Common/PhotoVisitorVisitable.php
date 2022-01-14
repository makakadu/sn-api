<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

interface PhotoVisitorVisitable { // Визитор для любого фото
    /** @return mixed */
    function accept(PhotoVisitor $visitor);
}
