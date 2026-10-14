<?php

namespace App\Controller\Donations;

use App\Entity\Donation;
use App\Enum\DonationStatus;
use App\Form\Donation\DonationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DonationController extends AbstractController
{
    #[Route('/faire-un-don', name: 'app_donation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $donation = new Donation();
        $donation->setStatus(DonationStatus::PAID);

        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($donation);
            $entityManager->flush();

            // Les coordonnées bancaires sont fictives : rien n'est transmis ni conservé.
            $this->addFlash('success', 'Merci pour votre don ! Votre soutien compte énormément pour le refuge.');

            return $this->redirectToRoute('app_donation_thanks');
        }

        return $this->render('Donation/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/faire-un-don/merci', name: 'app_donation_thanks', methods: ['GET'])]
    public function thanks(): Response
    {
        return $this->render('Donation/thanks.html.twig');
    }
}
