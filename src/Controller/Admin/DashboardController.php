<?php

namespace App\Controller\Admin;

use App\Repository\AdoptionRequestRepository;
use App\Repository\AnimalRepository;
use App\Repository\DonationRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AnimalRepository $animalRepository,
        private readonly UserRepository $userRepository,
        private readonly AdoptionRequestRepository $adoptionRequestRepository,
        private readonly DonationRepository $donationRepository,
    ) {
    }

    public function index(): Response
    {
        return $this->render('Admin/dashboard.html.twig', [
            'animal_count' => $this->animalRepository->count([]),
            'user_count' => $this->userRepository->count([]),
            'request_count' => $this->adoptionRequestRepository->count([]),
            'donation_count' => $this->donationRepository->count([]),
            'donation_total' => $this->donationRepository->getTotalAmount() / 100,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Refuge Animalier — Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-gauge');
        yield MenuItem::section('Refuge');
        yield MenuItem::linkTo(AnimalCrudController::class, 'Animaux', 'fa fa-paw');
        yield MenuItem::linkTo(AdoptionRequestCrudController::class, 'Demandes d\'adoption', 'fa fa-hand-holding-heart');
        yield MenuItem::linkTo(DonationCrudController::class, 'Dons', 'fa fa-donate');
        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');
        yield MenuItem::section();
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-globe', 'homepage');
    }
}
