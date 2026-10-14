<?php

namespace App\Controller\Adoptions;

use App\Entity\AdoptionRequest;
use App\Entity\User;
use App\Enum\AdoptionRequestStatus;
use App\Repository\AdoptionRequestRepository;
use App\Service\AdoptionRequestProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestion/demandes', name: 'app_adoption_manage_')]
#[IsGranted('ROLE_VOLUNTEER')]
class AdoptionManageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, AdoptionRequestRepository $adoptionRequestRepository): Response
    {
        $statusValue = (string) $request->query->get('status', '');
        $status = '' !== $statusValue ? AdoptionRequestStatus::tryFrom($statusValue) : null;

        return $this->render('Adoption/index.html.twig', [
            'requests' => $adoptionRequestRepository->findByStatus($status),
            'current_status' => $status,
            'status_choices' => AdoptionRequestStatus::cases(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(AdoptionRequest $adoptionRequest): Response
    {
        return $this->render('Adoption/show.html.twig', [
            'adoption_request' => $adoptionRequest,
            'status_choices' => AdoptionRequestStatus::cases(),
        ]);
    }

    #[Route('/{id}/statut', name: 'status', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function changeStatus(
        Request $request,
        AdoptionRequest $adoptionRequest,
        AdoptionRequestProcessor $processor,
    ): Response {
        $token = (string) $request->request->get('_token');

        if (!$this->isCsrfTokenValid('adoption-status-'.$adoptionRequest->getId(), $token)) {
            $this->addFlash('danger', 'Action non autorisée.');

            return $this->redirectToRoute('app_adoption_manage_show', ['id' => $adoptionRequest->getId()]);
        }

        $status = AdoptionRequestStatus::tryFrom((string) $request->request->get('status'));
        $volunteer = $this->getUser();

        if (null === $status || !$volunteer instanceof User) {
            $this->addFlash('danger', 'Statut invalide.');

            return $this->redirectToRoute('app_adoption_manage_show', ['id' => $adoptionRequest->getId()]);
        }

        $processor->changeStatus($adoptionRequest, $status, $volunteer);

        $this->addFlash('success', sprintf('La demande a été marquée « %s ».', $status->label()));

        return $this->redirectToRoute('app_adoption_manage_show', ['id' => $adoptionRequest->getId()]);
    }
}
