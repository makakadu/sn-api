<?php
declare(strict_types=1);
namespace App\Domain\Model;

use App\Domain\Model\SaveableVisitor;

interface Saveable {
    
    /**
     * @template T
     * @param SaveableVisitor <T> $visitor
     * @return T
     */
    function acceptSaveableVisitor(SaveableVisitor $visitor);
    
}
