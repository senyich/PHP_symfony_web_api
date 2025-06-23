<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Entity\User;
class UserDbService
{
    private UserRepository $repository;
    private SecurityService $securityService;
    public function __construct(
        UserRepository $repository, 
        SecurityService $securityService
    ) {
        $this->repository = $repository;
        $this->securityService = $securityService;
    }
    /**
     * Создает и сохраняет нового пользователя
     */
    public function createUser(string $name, string $plainPassword, bool $flush = false): User
    {
        $user = new User();
        $user->setName($name);
        
        // Хэшируем пароль
        $hashedPassword = $this->securityService->hashPassword($user, $plainPassword);
        $user->setPasswordHash($hashedPassword);

        $this->repository->save($user, $flush);

        return $user;
    }

    /**
     * Обновляет данные пользователя
     */
    public function updateUser(User $user, ?string $newPlainPassword = null, bool $flush = false): void
    {
        if ($newPlainPassword) {
            // Хэшируем новый пароль
            $hashedPassword = $this->securityService->hashPassword($user, $newPlainPassword);
            $user->setPasswordHash($hashedPassword);
        }

        $this->repository->update($user, $flush);
    }

    /**
     * Удаляет пользователя
     */
    public function deleteUser(User $user, bool $flush = false): void
    {
        $this->repository->remove($user, $flush);
    }
    /**
     * Находит пользователя по ID
     */
    public function findUser(int $id): ?User
    {
        return $this->repository->findUserById($id);
    }
    /**
     * Находит пользователя по имени (точное совпадение)
     */
    public function findUserByExactName(string $name): ?User
    {
        return $this->repository->findUserByName($name);
    }
    /**
     * Находит пользователей по части имени
     */
    public function findUsersByNameContains(string $partialName): array
    {
        return $this->repository->findUsersByPartialName($partialName);
    }
    /**
     * Возвращает всех пользователей
     */
    public function getAllUsers(): array
    {
        return $this->repository->findAllUsers();
    }
    /**
     * Возвращает количество пользователей
     */
    public function getUsersCount(): int
    {
        return $this->repository->countUsers();
    }
    /**
     * Проверяет, существует ли пользователь с таким именем
     */
    public function isUserExists(string $name): bool
    {
        return $this->repository->findUserByName($name) !== null;
    }
    /**
     * Сохраняет все изменения в базе данных
     */
    public function flush(): void
    {
        $this->repository->flush();
    }
}

?>