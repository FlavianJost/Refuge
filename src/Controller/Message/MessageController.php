<?php

namespace App\Controller\Message;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Form\Message\MessageType;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil/messages', name: 'app_message_')]
#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $conversation = $conversationRepository->findOneForUser($user);

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $conversation) {
                $conversation = new Conversation($user);
                $entityManager->persist($conversation);
            }

            $message->setConversation($conversation);
            $message->setAuthor($user);
            $message->setIsStaff(false);
            $entityManager->persist($message);

            $conversation->setLastMessageAt(new \DateTimeImmutable());
            $conversation->setUnreadForStaff($conversation->getUnreadForStaff() + 1);

            $entityManager->flush();

            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('app_message_index');
        }

        $this->markUserConversationAsRead($conversation, $entityManager);

        return $this->render('Message/index.html.twig', [
            'conversation' => $conversation,
            'messages' => null !== $conversation ? $messageRepository->findByConversation($conversation) : [],
            'form' => $form,
        ]);
    }

    #[Route('/nouveaux', name: 'latest', methods: ['GET'])]
    public function latest(
        Request $request,
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $conversation = $conversationRepository->findOneForUser($user);

        if (null === $conversation) {
            return $this->json(['html' => '', 'lastId' => 0]);
        }

        $afterId = $request->query->getInt('after');
        $messages = $messageRepository->findNewerThan($conversation, $afterId);

        if ([] !== $messages) {
            $this->markUserConversationAsRead($conversation, $entityManager);
        }

        return $this->json([
            'html' => $this->renderView('Message/_messages.html.twig', [
                'messages' => $messages,
                'viewerIsStaff' => false,
            ]),
            'lastId' => [] !== $messages ? end($messages)->getId() : $afterId,
        ]);
    }

    private function markUserConversationAsRead(?Conversation $conversation, EntityManagerInterface $entityManager): void
    {
        if (null !== $conversation && $conversation->getUnreadForUser() > 0) {
            $conversation->setUnreadForUser(0);
            $entityManager->flush();
        }
    }
}
