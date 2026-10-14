<?php

namespace App\Controller\Adoptions;

use App\Entity\AdoptionRequest;
use App\Entity\Animal;
use App\Entity\User;
use App\Enum\AdoptionRequestStatus;
use App\Form\Adoption\AdoptionRequestType;
use App\Repository\AdoptionRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdoptionRequestController extends AbstractController
{
    #[Route('/animaux/{id}/adopter', name: 'app_adoption_request_new', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        Animal $animal,
        AdoptionRequestRepository $adoptionRequestRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$animal->isAvailable()) {
            $this->addFlash('warning', 'Cet animal n\'est pas disponible à l\'adoption pour le moment.');

            return $this->redirectToRoute('app_animal_show', ['slug' => $animal->getSlug()]);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $adoptionRequest = new AdoptionRequest();
        $adoptionRequest->setAnimal($animal);
        $adoptionRequest->setUser($user);

        $form = $this->createForm(AdoptionRequestType::class, $adoptionRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $adoptionRequestRepository->findOneBy([
                'animal' => $animal,
                'user' => $user,
                'status' => AdoptionRequestStatus::PENDING,
            ]);

            if (null !== $existing) {
                $this->addFlash('info', 'Vous avez déjà une demande en attente pour cet animal.');

                return $this->redirectToRoute('app_animal_show', ['slug' => $animal->getSlug()]);
            }

            $entityManager->persist($adoptionRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande d\'adoption a bien été envoyée. Nous vous recontacterons rapidement.');

            return $this->redirectToRoute('app_animal_show', ['slug' => $animal->getSlug()]);
        }

        return $this->render('Adoption/new.html.twig', [
            'animal' => $animal,
            'form' => $form,
        ]);
    }
}
