<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;
//use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Domain\Repository;

abstract class AbstractDoctrineRepository implements Repository {
    protected EntityManager $entityManager;
    protected string $entityClass;

    public function __construct(EntityManager $em) {
        if (empty($this->entityClass)) {
            throw new \LogicException(
                get_class($this) . '::$entityClass is not defined'
            );
        }
        $this->entityManager = $em;
    }
    
    function getEM(): \Doctrine\ORM\EntityManager{
        return $this->entityManager;
    }

    function add(object $entity): void {
        $this->entityManager->persist($entity);
    }
    
    function getDeletedById($id) {
        $this->entityManager->getFilters()->disable('soft-deleteable');
        return $this->entityManager->find($this->entityClass, $id);
    }

    /** @return array<object> */
    function getAll(): array {
        return $this->entityManager->getRepository($this->entityClass)->findAll();
    }

    function getCount() : int {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb
            ->select('count(entity.id)')
            ->from($this->entityClass, 'entity')
            ->getQuery()
            //->useQueryCache(true)
            //->setResultCacheId('kek')
            //->useResultCache(true, 3600, 'kek')
            ->getSingleScalarResult();
        return (int)$result;
    }

//    function getPart(string $containerId, ?string $offsetId, int $limit) {
//        $count = $page * $limit - $limit;
//        return $this->entityManager->getRepository($this->entityClass)
//            ->findBy(array('walfindByl' => (int)$wallId), array(), $limit, $count);
//    }
        
//    public function getByOwnerId(string $ownerId) {
//        $qb = $this->entityManager->createQueryBuilder();
//        $result = $qb
//            ->select('o')
//            ->from($this->entityClass, 'o')
//            ->where('o.owner_id = :owner_id')
//            ->setParameter(':owner_id', $ownerId)
//            ->getQuery()
//            //->useQueryCache(true)
//            //->setResultCacheId('kek')
//            //->useResultCache(true, 3600, 'kek')
//            ->getOneOrNullResult();
//        return $result;
//    }

    /**
     * @param array<string> $ids
     * @return array<object>
     */
    function getByIds(array $ids): array {
        $repository = $this->entityManager->getRepository($this->entityClass);
        return $repository->findBy(array('id' => $ids));
    }
    
    function getBy(
        $conditions = [],
        $order = [],
        $limit = null,
        $offset = null
    ) {
        $repository = $this->entityManager->getRepository($this->entityClass);

        $results = $repository->findBy(
            $conditions,
            $order,
            $limit,
            $offset
        );

        return $results;
    }
    
    function flush(): void {
        $this->entityManager->flush();
    }

    /** @param mixed $entity */
    function remove($entity): void{
        $this->entityManager->remove($entity);
    }

    // ниже код из книги Clean Architecture in PHP
    //
    // public function begin() {
    //     $this->entityManager->beginTransaction();
    //     return $this;
    // }
    // public function commit() {
    //     $this->entityManager->flush();
    //     $this->entityManager->commit();
    //     return $this;
    // }
}
