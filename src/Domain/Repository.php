<?php
declare(strict_types=1);
namespace App\Domain;

interface Repository {
    function add(object $entity): void;
    
    /** @return array<object> */
    function getAll(): array;
    
    function getCount(): int;
    
    /** @param mixed $entity */
    function remove($entity): void;
    
    function flush(): void;
    
    function getEM(): \Doctrine\ORM\EntityManager;
    
//    /**
//     * @param array<string> $ids
//     * @return array<object>
//     */
//    function getByIds(array $ids): array;
}
