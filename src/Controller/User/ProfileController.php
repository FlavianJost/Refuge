<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\User\ProfileType;
use App\Service\UserPhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        ]);
    }

    #[Route('/profil/modifier', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPhotoUploader $photoUploader): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhoto($form->get('photoFile')->getData(), $user, $photoUploader);

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a bien été mis à jour.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('User/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    private function handlePhoto(?UploadedFile $file, User $user, UserPhotoUploader $uploader): void
    {
        if (null === $file) {
            return;
        }

        $uploader->remove($user->getPhoto());
        $user->setPhoto($uploader->upload($file));
    }
}
