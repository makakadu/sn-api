<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

interface Reactable { // Этот интерфейс нужен чтобы метод класса Reaction и его наследников мог возвратить сущность с которой связана реакция, а затем у этой сущности
    // вызвать acceptReactableVisitor метод
    
    /**
     * @template T
     * @param ReactableVisitor <T> $visitor
     * @return T
     */
    function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor);
    
}
