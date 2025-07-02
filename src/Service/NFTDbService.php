<?php

namespace App\Service;

use App\Entity\NFT;
use App\Repository\NFTRepository;
use App\Repository\UserRepository;

class NFTDbService
{
    private NFTRepository $repository;  
    public function __construct(NFTRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Создает и сохраняет новый NFT
     */
    public function createNFT(string $name, string $pattern, string $collection, bool $flush = false): NFT
    {
        $nft = new NFT();
        $nft->setName($name);
        $nft->setPattern($pattern);
        $nft->setCollection($collection);

        $this->repository->save($nft, $flush);

        return $nft;
    }

    /**
     * Обновляет NFT
     */
    public function updateNFT(NFT $nft, bool $flush = false): void
    {
        $this->repository->save($nft, $flush);
    }
    /**
     * Находит NFT по ID
     */
    public function findNFT(int $id): ?NFT
    {
        return $this->repository->findById($id);
    }

    /**
     * Находит все NFT с точным совпадением имени
     */
    public function findNFTsByName(string $name): array
    {
        return $this->repository->findByName($name);
    }

    /**
     * Находит NFT по части имени
     */
    public function findNFTsByPartialName(string $partialName): array
    {
        return $this->repository->findByPartialName($partialName);
    }

    /**
     * Находит NFT по паттерну
     */
    public function findNFTsByPattern(string $pattern): array
    {
        return $this->repository->findByPattern($pattern);
    }

    /**
     * Находит NFT по коллекции
     */
    public function findNFTsByCollection(string $collection): array
    {
        return $this->repository->findByCollection($collection);
    }

    /**
     * Возвращает все NFT с пагинацией
     */
    public function getPaginatedNFTs(int $page = 1, int $limit = 10): array
    {
        return $this->repository->findPaginated($page, $limit);
    }

    /**
     * Возвращает все NFT
     */
    public function getAllNFTs(): array
    {
        return $this->repository->findAllOrdered();
    }
    public function getNftByUserId(int $id): array
    {
        return $this->repository->findByUserId($id);
    }
    /**
     * Возвращает общее количество NFT
     */
    public function getNFTsCount(): int
    {
        return $this->repository->countAll();
    }

    /**
     * Проверяет, существует ли NFT с таким именем
     */
    public function isNFTExists(string $name): bool
    {
        return count($this->repository->findByName($name)) > 0;
    }

    /**
     * Принудительно сохраняет изменения в БД
     */
    public function flush(): void
    {
        $this->repository->flush();
    }

    /**
     * Связывает NFT с заказом
     */
    public function linkToOrder(NFT $nft, Order $order, bool $flush = false): void
    {
        $nft->setOrder($order);
        $this->repository->save($nft, $flush);
    }

    /**
     * Удаляет связь с заказом
     */
    public function unlinkFromOrder(NFT $nft, bool $flush = false): void
    {
        $nft->setOrder(null);
        $this->repository->save($nft, $flush);
    }
}