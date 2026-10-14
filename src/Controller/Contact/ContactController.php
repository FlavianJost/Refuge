<?php

namespace App\Controller\Contact;

use App\Entity\ContactMessage;
use App\Entity\User;
use App\Form\Contact\ContactMessageType;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ContactController extends AbstractController
{
    #[Route('/profil/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        ContactMessageRepository $contactMessageRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $contactMessage = new ContactMessage();
        $contactMessage->setUser($user);

        $form = $this->createForm(ContactMessageType::class, $contactMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contactMessage);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('Contact/new.html.twig', [
            'form' => $form,
            'messages' => $contactMessageRepository->findByUser($user),
        ]);
    }
}
