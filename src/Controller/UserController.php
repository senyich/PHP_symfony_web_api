<?php

namespace App\Controller;

use App\Service\SecurityService;
use App\Service\UserDbService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    private UserDbService $userDbService;
    private SecurityService $securityService;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        UserDbService $userDbService, 
        SecurityService $securityService, 
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
        $this->userDbService = $userDbService;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['name']) || !isset($data['password'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: name, password'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        if ($this->userDbService->isUserExists($data['name'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Username already exists'
            ], Response::HTTP_CONFLICT);
        }
        
        $user = $this->userDbService->createUser(
            $data['name'],
            $data['password'],
            true
        );
        
        $token = bin2hex(random_bytes(32));
        $user->setAuthToken($token);
        $this->entityManager->flush();
    
        return $this->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $this->serializeUser($user)
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['name']) || !isset($data['password'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: name, password'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $user = $this->userDbService->findUserByExactName($data['name']);
        
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        if (!$this->securityService->verifyPassword($user, $data['password'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        $token = bin2hex(random_bytes(32));
        $user->setAuthToken($token);
        $this->entityManager->flush();
        
        return $this->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $this->serializeUser($user)
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        
        if (!$token) {
            return $this->json([
                'status' => 'error',
                'message' => 'Authorization token required'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $user = $this->userDbService->findUserByToken($token);
        
        if ($user) {
            $user->setAuthToken(null);
            $this->entityManager->flush();
        }
        
        return $this->json([
            'status' => 'success',
            'message' => 'Logout successful'
        ]);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'orders_count' => count($user->getOrders()),
            'token' => $user->getAuthToken()
        ];
    }
}