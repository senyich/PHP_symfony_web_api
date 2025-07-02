<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\OrderDbService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/order')]
final class OrderController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private OrderDbService $orderDbService;
    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        OrderDbService $orderDbService) {
        $this->entityManager = $entityManagerInterface;
        $this->orderDbService = $orderDbService;
    }
   #[Route('/orders', name: 'get_orders', methods: ['GET'])]
    public function getOrders(Request $request): JsonResponse
    {
        $orders = $this->orderDbService->getAllOrders();
        
        $serializedOrders = array_map(function (Order $order): array {
            return [
                'id' => $order->getId(),
                'price' => $order->getPrice(),
                'createdTime' => $order->getCreatedTime() ? $order->getCreatedTime()->format('Y-m-d H:i:s') : null,
                'owner' => $order->getOwner() ? [
                    'id' => $order->getOwner()->getId(),
                    'name' => $order->getOwner()->getName(),
                    'balance' => $order->getOwner()->getBalance()
                ] : null,
                'nft' => $order->getNft() ? [
                    'id' => $order->getNft()->getId(),
                    'name' => $order->getNft()->getName(),
                    'pattern' => $order->getNft()->getPattern(),
                    'collection' => $order->getNft()->getCollection(),
                    'currentOwnerId' => $order->getNft()->getOwner() ? $order->getNft()->getOwner()->getId() : null
                ] : null,
                'status' => $order->getNft() && $order->getNft()->getOwner() !== $order->getOwner() 
                    ? 'completed' 
                    : 'active'
            ];
        }, $orders);

        return $this->json([
            'status' => 'success',
            'data' => [
                'orders' => $serializedOrders,
                'count' => count($serializedOrders)
            ]
        ]);
    }
}
