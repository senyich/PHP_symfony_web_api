<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    /**
     * Сохраняет нового пользователя в базе данных.
     * 
     * @param User $user Объект пользователя для сохранения
     * @param bool $flush Если true, сразу выполняет flush (сохранение изменений)
     * @return void
     */
    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * Обновляет существующего пользователя в базе данных.
     * 
     * @param User $user Объект пользователя для обновления
     * @param bool $flush Если true, сразу выполняет flush (сохранение изменений)
     * @return void
     */
    public function update(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Удаляет пользователя из базы данных.
     * 
     * @param User $user Объект пользователя для удаления
     * @param bool $flush Если true, сразу выполняет flush (сохранение изменений)
     * @return void
     */
    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * Возвращает массив всех пользователей, отсортированных по возрастанию ID.
     * 
     * @return User[] Массив объектов пользователей
     */
    public function findAllUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Находит пользователя по его уникальному идентификатору (ID).
     * 
     * @param int $id ID пользователя
     * @return User|null Объект пользователя или null, если не найден
     */
    public function findUserById(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Находит пользователя по точному совпадению имени.
     * 
     * @param string $name Имя пользователя
     * @return User|null Объект пользователя или null, если не найден
     */
    public function findUserByName(string $name): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Находит пользователей, у которых имя содержит заданную подстроку.
     * 
     * @param string $partialName Подстрока для поиска в имени
     * @return User[] Массив объектов пользователей, отсортированных по имени
     */
    public function findUsersByPartialName(string $partialName): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.name LIKE :partialName')
            ->setParameter('partialName', '%' . $partialName . '%')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Подсчитывает общее количество пользователей в базе данных.
     * 
     * @return int Количество пользователей
     */
    public function countUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
