<?php

namespace App\Repository;

use App\Entity\NFT;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NFT>
 */
class NFTRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NFT::class);
    }

    /**
     * Сохраняет новый NFT в базе данных.
     * 
     * @param NFT $nft Объект NFT для сохранения
     * @param bool $flush Если true, сразу выполняет flush
     * @return void
     */
    public function save(NFT $nft, bool $flush = false): void
    {
        $this->getEntityManager()->persist($nft);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Удаляет NFT из базы данных.
     * 
     * @param NFT $nft Объект NFT для удаления
     * @param bool $flush Если true, сразу выполняет flush
     * @return void
     */
    public function remove(NFT $nft, bool $flush = false): void
    {
        $this->getEntityManager()->remove($nft);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Возвращает все NFT, отсортированные по ID.
     * 
     * @return NFT[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит NFT по ID.
     * 
     * @param int $id
     * @return NFT|null
     */
    public function findById(int $id): ?NFT
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Находит NFT по имени.
     * 
     * @param string $name
     * @return NFT[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.name = :name')
            ->setParameter('name', $name)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит NFT по части имени.
     * 
     * @param string $partialName
     * @return NFT[]
     */
    public function findByPartialName(string $partialName): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.name LIKE :partialName')
            ->setParameter('partialName', '%'.$partialName.'%')
            ->orderBy('n.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит NFT по паттерну.
     * 
     * @param string $pattern
     * @return NFT[]
     */
    public function findByPattern(string $pattern): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.pattern = :pattern')
            ->setParameter('pattern', $pattern)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит NFT по коллекции.
     * 
     * @param string $collection
     * @return NFT[]
     */
    public function findByCollection(string $collection): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.collection = :collection')
            ->setParameter('collection', $collection)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Возвращает NFT с пагинацией.
     * 
     * @param int $page
     * @param int $limit
     * @return NFT[]
     */
    public function findPaginated(int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.owner = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Ищет все NFT по айди владельца
     * 
     * @param int $userId Айди владельца
     * @return NFT[] Массив с NFT
     */
    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->join('n.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Подсчитывает общее количество NFT.
     * 
     * @return int
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}