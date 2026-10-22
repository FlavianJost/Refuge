<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\User\DeleteAccountType;
use App\Form\User\ProfileType;
use App\Repository\ConversationRepository;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('User/profile.html.twig', [
            'user' => $user,
            'delete_form' => $this->createForm(DeleteAccountType::class),
        ]);
    }

    #[Route('/profil/modifier', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        #[Autowire(service: 'app.photo_uploader.users')] PhotoUploader $photoUploader,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePassword($form, $user, $passwordHasher);

            if ($form->isValid()) {
                $this->handlePhoto($form->get('photoFile')->getData(), $user, $photoUploader);

                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a bien été mis à jour.');

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('User/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profil/supprimer', name: 'app_profile_delete', methods: ['POST'])]
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $entityManager,
        #[Autowire(service: 'app.photo_uploader.users')] PhotoUploader $photoUploader,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(DeleteAccountType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'La suppression du compte a échoué.');

            return $this->redirectToRoute('app_profile');
        }

        $password = (string) $form->get('password')->getData();

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash('danger', 'Le mot de passe est incorrect.');

            return $this->redirectToRoute('app_profile');
        }

        $photo = $user->getPhoto();

        $entityManager->remove($user);
        $entityManager->flush();

        $photoUploader->remove($photo);

        $request->getSession()->invalidate();
        $this->addFlash('success', 'Votre compte et vos données personnelles ont bien été supprimés.');

        return $this->redirectToRoute('homepage');
    }

    #[Route('/profil/export', name: 'app_profile_export', methods: ['GET'])]
    public function exportData(ConversationRepository $conversationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $messages = [];
        $conversation = $conversationRepository->findOneForUser($user);

        if (null !== $conversation) {
            foreach ($conversation->getMessages() as $message) {
                $messages[] = [
                    'auteur' => $message->isStaff() ? 'Refuge' : 'Vous',
                    'texte' => $message->getBody(),
                    'date' => $message->getCreatedAt()->format(\DateTimeInterface::ATOM),
                ];
            }
        }

        $adoptionRequests = [];

        foreach ($user->getAdoptionRequests() as $adoptionRequest) {
            $events = [];

            foreach ($adoptionRequest->getEvents() as $event) {
                $events[] = [
                    'titre' => $event->title(),
                    'statut' => $event->getStatus()->value,
                    'note' => $event->getNote(),
                    'date' => $event->getCreatedAt()->format(\DateTimeInterface::ATOM),
                ];
            }

            $appointments = [];

            foreach ($adoptionRequest->getAppointments() as $appointment) {
                $appointments[] = [
                    'date_proposee' => $appointment->getProposedAt()?->format(\DateTimeInterface::ATOM),
                    'statut' => $appointment->getStatus()->value,
                    'note' => $appointment->getNote(),
                    'reponse' => $appointment->getResponse(),
                ];
            }

            $adoptionRequests[] = [
                'animal' => $adoptionRequest->getAnimal()?->getName(),
                'statut' => $adoptionRequest->getStatus()->value,
                'date' => $adoptionRequest->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'message' => $adoptionRequest->getMessage(),
                'questionnaire' => [
                    'logement' => $adoptionRequest->getHousing()->value,
                    'jardin' => $adoptionRequest->hasGarden(),
                    'autres_animaux' => $adoptionRequest->hasOtherPets(),
                    'enfants' => $adoptionRequest->hasChildren(),
                    'experience' => $adoptionRequest->getExperience(),
                ],
                'parcours' => $events,
                'rendez_vous' => $appointments,
            ];
        }

        $reviews = [];

        foreach ($user->getReviews() as $review) {
            $reviews[] = [
                'note' => $review->getRating(),
                'commentaire' => $review->getComment(),
                'date' => $review->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        $logins = [];

        foreach ($user->getLoginRecords() as $loginRecord) {
            $logins[] = [
                'adresse_ip' => $loginRecord->getIpAddress(),
                'date' => $loginRecord->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        $data = [
            'exporte_le' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'profil' => [
                'email' => $user->getEmail(),
                'prenom' => $user->getFirstName(),
                'nom' => $user->getLastName(),
                'roles' => $user->getRoles(),
                'inscrit_le' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'derniere_connexion' => $user->getLastLoginAt()?->format(\DateTimeInterface::ATOM),
                'double_authentification' => $user->isTotpAuthenticationEnabled(),
            ],
            'demandes_adoption' => $adoptionRequests,
            'avis' => $reviews,
            'messages' => $messages,
            'historique_connexions' => $logins,
        ];

        $json = json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);

        return new Response($json, Response::HTTP_OK, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="refuge-mes-donnees.json"',
        ]);
    }

    private function handlePassword(FormInterface $form, User $user, UserPasswordHasherInterface $passwordHasher): void
    {
        $currentPassword = (string) $form->get('currentPassword')->getData();
        $newPassword = (string) $form->get('plainPassword')->getData();

        if ('' === $currentPassword && '' === $newPassword) {
            return;
        }

        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $form->get('currentPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));

            return;
        }

        if (\mb_strlen($newPassword) < 6) {
            $form->get('plainPassword')->addError(new FormError('Le nouveau mot de passe doit contenir au moins 6 caractères.'));

            return;
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
    }

    private function handlePhoto(?UploadedFile $file, User $user, PhotoUploader $uploader): void
    {
        if (null === $file) {
            return;
        }

        $uploader->remove($user->getPhoto());
        $user->setPhoto($uploader->upload($file));
    }
}
