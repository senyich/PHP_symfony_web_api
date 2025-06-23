<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NFTController extends AbstractController
{
    #[Route('/nft', name: 'app_nft')]
    public function index(): Response
    {
    }
}
