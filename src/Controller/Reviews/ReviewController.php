<?php

namespace App\Controller\Reviews;

use App\Entity\Review;
use App\Entity\User;
use App\Form\Review\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewController extends AbstractController
{
    #[Route('/avis', name: 'app_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        return $this->render('Review/index.html.twig', [
            'reviews' => $reviewRepository->findLatest(50),
            'average' => $reviewRepository->getAverageRating(),
            'total' => $reviewRepository->count([]),
        ]);
    }

    #[Route('/avis/nouveau', name: 'app_review_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $review = new Review();
        $review->setUser($user);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis !');

            return $this->redirectToRoute('app_review_index');
        }

        return $this->render('Review/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/avis/{id}/supprimer', name: 'app_review_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($review->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete-review-'.$review->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();

            $this->addFlash('success', 'L\'avis a bien été supprimé.');
        } else {
            $this->addFlash('danger', 'Action non autorisée.');
        }

        return $this->redirectToRoute('app_review_index');
    }
}
