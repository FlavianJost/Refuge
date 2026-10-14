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

#[Route('/gestion/messages', name: 'app_message_manage_')]
#[IsGranted('ROLE_VOLUNTEER')]
class MessageManageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ConversationRepository $conversationRepository): Response
    {
        return $this->render('Message/manage.html.twig', [
            'conversations' => $conversationRepository->findAllRecent(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(
        Request $request,
        Conversation $conversation,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $staff = $this->getUser();

        if (!$staff instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setConversation($conversation);
            $message->setAuthor($staff);
            $message->setIsStaff(true);
            $entityManager->persist($message);

            $conversation->setLastMessageAt(new \DateTimeImmutable());
            $conversation->setUnreadForUser($conversation->getUnreadForUser() + 1);

            $entityManager->flush();

            $this->addFlash('success', 'Votre réponse a bien été envoyée.');

            return $this->redirectToRoute('app_message_manage_show', ['id' => $conversation->getId()]);
        }

        $this->markStaffConversationAsRead($conversation, $entityManager);

        return $this->render('Message/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messageRepository->findByConversation($conversation),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/nouveaux', name: 'latest', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function latest(
        Request $request,
        Conversation $conversation,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $afterId = $request->query->getInt('after');
        $messages = $messageRepository->findNewerThan($conversation, $afterId);

        if ([] !== $messages) {
            $this->markStaffConversationAsRead($conversation, $entityManager);
        }

        return $this->json([
            'html' => $this->renderView('Message/messages.html.twig', [
                'messages' => $messages,
                'viewerIsStaff' => true,
            ]),
            'lastId' => [] !== $messages ? end($messages)->getId() : $afterId,
        ]);
    }

    private function markStaffConversationAsRead(Conversation $conversation, EntityManagerInterface $entityManager): void
    {
        if ($conversation->getUnreadForStaff() > 0) {
            $conversation->setUnreadForStaff(0);
            $entityManager->flush();
        }
    }
}
