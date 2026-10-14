<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        return $this->render('home.html.twig', [
            'latest_reviews' => $reviewRepository->findLatest(3),
            'review_average' => $reviewRepository->getAverageRating(),
            'review_count' => $reviewRepository->count([]),
        ]);
    }
}
