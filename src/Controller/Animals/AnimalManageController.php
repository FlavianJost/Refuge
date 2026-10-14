<?php

namespace App\Controller\Animals;

use App\Entity\Animal;
use App\Form\Animal\AnimalType;
use App\Repository\AnimalRepository;
use App\Service\AnimalPhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestion/animaux', name: 'app_animal_manage_')]
#[IsGranted('ROLE_VOLUNTEER')]
class AnimalManageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(AnimalRepository $animalRepository): Response
    {
        return $this->render('Animal/manage/index.html.twig', [
            'animals' => $animalRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AnimalPhotoUploader $photoUploader): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhoto($form->get('photoFile')->getData(), $animal, $photoUploader);

            $entityManager->persist($animal);
            $entityManager->flush();

            $this->addFlash('success', sprintf('L\'animal « %s » a bien été ajouté.', $animal->getName()));

            return $this->redirectToRoute('app_animal_manage_index');
        }

        return $this->render('Animal/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Animal $animal, EntityManagerInterface $entityManager, AnimalPhotoUploader $photoUploader): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhoto($form->get('photoFile')->getData(), $animal, $photoUploader);
            $animal->touch();

            $entityManager->flush();

            $this->addFlash('success', sprintf('L\'animal « %s » a bien été mis à jour.', $animal->getName()));

            return $this->redirectToRoute('app_animal_manage_index');
        }

        return $this->render('Animal/edit.html.twig', [
            'animal' => $animal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Animal $animal, EntityManagerInterface $entityManager, AnimalPhotoUploader $photoUploader): Response
    {
        if ($this->isCsrfTokenValid('delete-animal-'.$animal->getId(), (string) $request->request->get('_token'))) {
            $photoUploader->remove($animal->getPhoto());

            $entityManager->remove($animal);
            $entityManager->flush();

            $this->addFlash('success', 'L\'animal a bien été supprimé.');
        } else {
            $this->addFlash('danger', 'Action non autorisée.');
        }

        return $this->redirectToRoute('app_animal_manage_index');
    }

    private function handlePhoto(?UploadedFile $file, Animal $animal, AnimalPhotoUploader $uploader): void
    {
        if (null === $file) {
            return;
        }

        $uploader->remove($animal->getPhoto());
        $animal->setPhoto($uploader->upload($file));
    }
}
