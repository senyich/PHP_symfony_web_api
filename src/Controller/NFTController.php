<?php

namespace App\Controller;

use App\Service\NFTDbService;
use App\Entity\NFT;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/nfts')]
final class NFTController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private NFTDbService $nftDbService;
    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        NFTDbService $nftDbService) {
        $this->entityManager = $entityManagerInterface;
        $this->nftDbService = $nftDbService;
    }
    
    #[Route('/add-nft', name: 'app_nft', methods: ['POST'])]
     public function addNft(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['pattern']) || !isset($data['collection']) || !isset($data['owner_id'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Missing required fields: name, pattern, collection, owner_id'
            ], Response::HTTP_BAD_REQUEST);
        }
        $owner = $this->entityManager->getRepository(User::class)->find($data['owner_id']);
        if (!$owner) {
            return $this->json([
                'status' => 'error',
                'message' => 'Owner not found'
            ], Response::HTTP_NOT_FOUND);
        }
        try {
            $nft = $this->nftDbService->createNFT(
                $data['name'],
                $data['pattern'],
                $data['collection'],
                true
            );
            
            $nft->setOwner($owner);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'NFT created successfully',
                'nft' => [
                    'id' => $nft->getId(),
                    'name' => $nft->getName(),
                    'pattern' => $nft->getPattern(),
                    'collection' => $nft->getCollection(),
                    'owner_id' => $owner->getId()
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to create NFT: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
