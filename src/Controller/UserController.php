<?php

namespace App\Controller;

use App\Service\NFTDbService;
use OpenApi\Attributes as OA;
use App\Service\OrderDbService;
use App\Service\SecurityService;
use App\Service\UserDbService;
use App\Entity\User;
use App\Entity\NFT;
use App\Entity\Order;
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
    private NFTDbService $nftDbService;
    private UserDbService $userDbService;
    private OrderDbService $orderDbService;
    private SecurityService $securityService;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        UserDbService $userDbService, 
        SecurityService $securityService, 
        EntityManagerInterface $entityManager,
        OrderDbService $orderDbService,
        NFTDbService $nftDbService
    ) {
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
        $this->userDbService = $userDbService;
        $this->orderDbService = $orderDbService;
        $this->nftDbService = $nftDbService;
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
        
        $token = $this->securityService->generateJWTToken([
            'user_id' => $user->getId(),
            'username' => $user->getName()
        ]);
    
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
        
        $token = $this->securityService->generateJWTToken([
            'user_id' => $user->getId(),
            'username' => $user->getName()
        ]);
        
        return $this->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $this->serializeUser($user)
        ]);
    }
    #[Route('/buy-order/{orderId}', name: 'buy_order', methods: ['POST'])]
    public function buyOrder(Request $request, int $orderId): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return $this->json([
                'status' => 'error',
                'message' => 'Authorization token required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $claims = $this->securityService->parseJWTToken($token);
        
        if (!$claims || !isset($claims['user_id'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid or expired token'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        $buyer = $this->userDbService->findUser($claims['user_id']);
        if (!$buyer) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $order = $this->entityManager->getRepository(Order::class)->find($orderId);
        if (!$order) {
            return $this->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($order->getOwner()->getId() === $buyer->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => 'You cannot buy your own order'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($buyer->getBalance() < $order->getPrice()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Insufficient funds'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->entityManager->beginTransaction();
            
            $this->userDbService->buyOrder($order, $buyer->getId());
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->json([
                'status' => 'success',
                'message' => 'Order purchased successfully',
                'nft' => [
                    'id' => $order->getNft()->getId(),
                    'name' => $order->getNft()->getName(),
                    'new_owner_id' => $buyer->getId()
                ],
                'balance' => $buyer->getBalance()
            ]);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to buy order: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/publish-order', name: 'publish_order', methods: ['POST'])]
    public function publishOrder(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return $this->json([
                'status' => 'error',
                'message' => 'Authorization token required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $claims = $this->securityService->parseJWTToken($token);
        
        $user = $this->userDbService->findUser($claims['user_id']);
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (!isset($data['price']) || !isset($data['nft_id'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: price, nft_id'
            ], Response::HTTP_BAD_REQUEST);
        }
        $nft = $this->entityManager->getRepository(NFT::class)->find($data['nft_id']);
        if (!$nft) {
            return $this->json([
                'status' => 'error',
                'message' => 'NFT not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($nft->getOwner() !== $user) {
            return $this->json([
                'status' => 'error',
                'message' => 'You are not the owner of this NFT'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $order = $this->orderDbService->createOrder(
                $data['price'],
                $user,
                $nft,
                new \DateTime(),
                true
            );
            return $this->json([
                'status' => 'success',
                'message' => 'Order published successfully',
                'order' => [
                    'id' => $order->getId(),
                    'price' => $order->getPrice(),
                    'created_time' => $order->getCreatedTime()->format('Y-m-d H:i:s'),
                    'nft_id' => $nft->getId()
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to publish order: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/get-user-fullinfo', name: 'user_full_info', methods: ['GET'])]
    public function getUserFullInfo(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return $this->json([
                'status' => 'error',
                'message' => 'Authorization token required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        
        //TODO: не работает проверка токена, исправить
        // if (!$this->securityService->validateJWTToken($token)) {
        //     return $this->json([
        //         'status' => 'error',
        //         'message' => 'Invalid token'
        //     ], Response::HTTP_UNAUTHORIZED);
        // }
        
        $claims = $this->securityService->parseJWTToken($token);
        $user = $this->userDbService->findUser($claims['user_id']);
        
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $orders = $this->orderDbService->findUserOrders($user->getId());
        $nfts = $this->nftDbService->getNftByUserId($user->getId());
        $userData = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'ordersCount' => count($orders)
        ];
        $ordersData = [];
        $nftsData = [];
        foreach($nfts as $nft){
            $nftsData[] =  [
                'id' => $nft->getId(),
                'name' => $nft->getName(),
                'pattern' => $nft->getPattern(),
                'collection' => $nft->getCollection(),
                'owner_id' => $nft->getOwner() ? $nft->getOwner()->getId() : null,
                'order_id' => $nft->getOrder() ? $nft->getOrder()->getId() : null
            ];
        }
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
            'orders' => $ordersData,
            'nfts' => $nftsData
        ]);
    }
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return $this->json([
                'status' => 'error',
                'message' => 'Authorization token required'
            ], Response::HTTP_BAD_REQUEST);
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
            'orders_count' => count($user->getOrders())
        ];
    }
}