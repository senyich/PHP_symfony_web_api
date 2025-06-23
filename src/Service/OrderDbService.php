<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\NFT;
use App\Repository\OrderRepository;
use DateTime;

class OrderDbService
{
    private OrderRepository $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Создает и сохраняет новый заказ
     */
    public function createOrder(
        string $price,
        User $owner,
        NFT $nft,
        DateTime $createdTime = null,
        bool $flush = false
    ): Order {
        $order = new Order();
        $order->setPrice($price);
        $order->setOwner($owner);
        $order->setNft($nft);
        $order->setCreatedTime($createdTime ?? new DateTime());

        $this->repository->save($order, $flush);

        return $order;
    }

    /**
     * Обновляет заказ
     */
    public function updateOrder(Order $order, bool $flush = false): void
    {
        $this->repository->update($order, $flush);
    }

    /**
     * Удаляет заказ
     */
    public function deleteOrder(Order $order, bool $flush = false): void
    {
        $this->repository->remove($order, $flush);
    }

    /**
     * Находит заказ по ID
     */
    public function findOrder(int $id): ?Order
    {
        return $this->repository->findOrderById($id);
    }

    /**
     * Возвращает все заказы
     */
    public function getAllOrders(): array
    {
        return $this->repository->findAllOrders();
    }

    /**
     * Находит заказы пользователя
     */
    public function findUserOrders(int $userId): array
    {
        return $this->repository->findOrdersByUserId($userId);
    }

    /**
     * Находит заказы по статусу
     */
    public function findOrdersByStatus(string $status): array
    {
        return $this->repository->findOrdersByStatus($status);
    }

    /**
     * Находит заказы за период
     */
    public function findOrdersBetweenDates(DateTime $startDate, DateTime $endDate): array
    {
        return $this->repository->findOrdersByDateRange($startDate, $endDate);
    }

    /**
     * Возвращает заказы с пагинацией
     */
    public function getPaginatedOrders(int $page = 1, int $limit = 10): array
    {
        return $this->repository->findOrdersWithPagination($page, $limit);
    }

    /**
     * Возвращает количество заказов
     */
    public function getOrdersCount(): int
    {
        return $this->repository->countOrders();
    }

    /**
     * Обновляет цену заказа
     */
    public function updateOrderPrice(Order $order, string $newPrice, bool $flush = false): void
    {
        $order->setPrice($newPrice);
        $this->repository->update($order, $flush);
    }

    /**
     * Связывает заказ с NFT
     */
    public function linkOrderToNft(Order $order, NFT $nft, bool $flush = false): void
    {
        $order->setNft($nft);
        $this->repository->update($order, $flush);
    }

    /**
     * Изменяет владельца заказа
     */
    public function changeOrderOwner(Order $order, User $newOwner, bool $flush = false): void
    {
        $order->setOwner($newOwner);
        $this->repository->update($order, $flush);
    }

    /**
     * Принудительно сохраняет изменения
     */
    public function flush(): void
    {
        $this->repository->flush();
    }
}