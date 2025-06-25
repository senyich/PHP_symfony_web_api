<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Service\OrderDbService;
use App\Service\SecurityService;
use App\Service\UserDbService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[OA\Tag(name: "Authentication")]
#[Route('/api/users')]
final class UserController extends AbstractController
{
    private UserDbService $userDbService;
    private OrderDbService $orderDbService;
    private SecurityService $securityService;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        UserDbService $userDbService, 
        SecurityService $securityService, 
        EntityManagerInterface $entityManager,
        OrderDbService $orderDbService
    ) {
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
        $this->userDbService = $userDbService;
        $this->orderDbService = $orderDbService;
    }
    #[OA\Post(
        summary: "Регистрация пользователя",
        requestBody: new OA\RequestBody(
            description: "Данные для регистрации",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "password", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Успешная регистрация"),
            new OA\Response(response: 400, description: "Недостаточно данных"),
            new OA\Response(response: 409, description: "Имя занято")
        ]
    )]
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
    #[OA\Post(
        summary: "Авторизация пользователя",
        requestBody: new OA\RequestBody(
            description: "Креденшелы",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "password", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Успешный вход"),
            new OA\Response(response: 400, description: "Недостаточно данных"),
            new OA\Response(response: 401, description: "Неверные креденшелы")
        ]
    )]
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
    #[OA\Get(
        summary: "Полная информация о пользователе",
        parameters: [new OA\Parameter(
            name: "Authorization",
            in: "header",
            required: true,
            schema: new OA\Schema(type: "string")
        )],
        responses: [
            new OA\Response(response: 200, description: "Данные пользователя + заказы"),
            new OA\Response(response: 400, description: "Требуется токен"),
            new OA\Response(response: 404, description: "Пользователь не найден")
        ]
    )]
    #[Route('/get-user-fullinfo', name: 'user_full_info', methods: ['GET'])]
    public function getUserFullInfo(Request $request) : JsonResponse
    {
        $token = $request->headers->get('Authorization');
        if (!$token) {
            return $this->json([
                'status' => 'error',
                'message' => 'Authorization token required'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $user = $this->userDbService->findUserByToken($token);
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $orders = $this->orderDbService->findUserOrders($user->getId());
        
        $userData = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'authToken' => $user->getAuthToken(),
            'ordersCount' => count($orders)
        ];
        $ordersData = [];
        foreach ($orders as $order) {
            $nft = $order->getNft();
            
            $ordersData[] = [
                'id' => $order->getId(),
                'price' => $order->getPrice(),
                'createdTime' => $order->getCreatedTime()->format('Y-m-d H:i:s'),
                'nft' => [
                    'id' => $nft->getId(),
                ]
            ];
        }
        return $this->json([
            'status' => 'success',
            'user' => $userData,
            'orders' => $ordersData
        ]);
    }
    #[OA\Post(
        summary: "Выход из системы",
        parameters: [new OA\Parameter(
            name: "Authorization",
            in: "header",
            required: true,
            schema: new OA\Schema(type: "string")
        )],
        responses: [
            new OA\Response(response: 200, description: "Сессия завершена"),
            new OA\Response(response: 400, description: "Требуется токен")
        ]
    )]
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