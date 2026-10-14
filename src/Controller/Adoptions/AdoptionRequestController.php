<?php

namespace App\Controller\Adoptions;

use App\Entity\AdoptionRequest;
use App\Entity\AdoptionRequestEvent;
use App\Entity\Animal;
use App\Entity\User;
use App\Entity\VisitAppointment;
use App\Enum\AdoptionRequestStatus;
use App\Form\Adoption\AdoptionRequestType;
use App\Form\Adoption\VisitAppointmentType;
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

            $adoptionRequest->addEvent(
                (new AdoptionRequestEvent())
                    ->setStatus(AdoptionRequestStatus::PENDING)
                    ->setAuthor($user),
            );

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

    #[Route('/profil/adoptions/{id}', name: 'app_adoption_request_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function show(AdoptionRequest $adoptionRequest): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User || $adoptionRequest->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $visitForm = $this->createForm(VisitAppointmentType::class, new VisitAppointment());

        return $this->render('Adoption/detail.html.twig', [
            'adoption_request' => $adoptionRequest,
            'visit_form' => $visitForm,
        ]);
    }

    #[Route('/profil/adoptions/{id}/rendez-vous', name: 'app_visit_request_new', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function requestVisit(
        Request $request,
        AdoptionRequest $adoptionRequest,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User || $adoptionRequest->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $appointment = new VisitAppointment();
        $form = $this->createForm(VisitAppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adoptionRequest->addAppointment($appointment);
            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de rendez-vous a bien été envoyée.');
        } else {
            $this->addFlash('danger', 'Le rendez-vous n\'a pas pu être enregistré. Merci de vérifier la date proposée.');
        }

        return $this->redirectToRoute('app_adoption_request_show', ['id' => $adoptionRequest->getId()]);
    }
}
