<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }
    /**
     * Сохраняет новый заказ в базе данных.
     * 
     * @param Order $order Объект заказа для сохранения
     * @param bool $flush Если true, сразу выполняет flush (сохранение изменений)
     * @return void
     */
    public function save(Order $order, bool $flush = false): void
    {
        $this->getEntityManager()->persist($order);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * Обновляет существующий заказ в базе данных.
     * 
     * @param Order $order Объект заказа для обновления
     * @param bool $flush Если true, сразу выполняет flush (сохранение изменений)
     * @return void
     */
    public function update(Order $order, bool $flush = false): void
    {
        $this->getEntityManager()->persist($order);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * Удаляет заказ из базы данных.
     * 
     * @param Order $order Объект заказа для удаления
     * @param bool $flush Если true, сразу выполняет flush (сохранение изменений)
     * @return void
     */
    public function remove(Order $order, bool $flush = false): void
    {
        $this->getEntityManager()->remove($order);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * Возвращает массив всех заказов, отсортированных по возрастанию ID.
     * 
     * @return Order[] Массив объектов заказов
     */
    public function findAllOrders(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Находит заказ по его уникальному идентификатору (ID).
     * 
     * @param int $id ID заказа
     * @return Order|null Объект заказа или null, если не найден
     */
    public function findOrderById(int $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Находит заказы по конкретному пользователю.
     * 
     * @param int $userId ID пользователя
     * @return Order[] Массив заказов пользователя
     */
    public function findOrdersByUserId(int $userId): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.owner', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит заказы по статусу.
     * 
     * @param string $status Статус заказа
     * @return Order[] Массив заказов с указанным статусом
     */
    public function findOrdersByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Находит заказы, созданные в указанный период.
     * 
     * @param \DateTime $startDate Начало периода
     * @param \DateTime $endDate Конец периода
     * @return Order[] Массив заказов за период
     */
    public function findOrdersByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Подсчитывает общее количество заказов в базе данных.
     * 
     * @return int Количество заказов
     */
    public function countOrders(): int
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Возвращает заказы с пагинацией.
     * 
     * @param int $page Номер страницы
     * @param int $limit Количество элементов на странице
     * @return Order[] Массив заказов
     */
    public function findOrdersWithPagination(int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}