<?php

namespace App\Controller\Animals;

use App\Entity\Animal;
use App\Entity\AnimalPhoto;
use App\Form\Animal\AnimalType;
use App\Repository\AnimalRepository;
use App\Service\AnimalPhotoUploader;
use App\Service\AnimalSlugger;
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
        return $this->render('Animal/manage.html.twig', [
            'animals' => $animalRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AnimalPhotoUploader $photoUploader, AnimalSlugger $animalSlugger): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animal->setSlug($animalSlugger->generate($animal));
            $this->handlePhoto($form->get('photoFile')->getData(), $animal, $photoUploader);
            $this->handleGalleryPhotos($form->get('photosFiles')->getData(), $animal, $photoUploader);

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
    public function edit(Request $request, Animal $animal, EntityManagerInterface $entityManager, AnimalPhotoUploader $photoUploader, AnimalSlugger $animalSlugger): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animal->setSlug($animalSlugger->generate($animal));
            $this->handlePhoto($form->get('photoFile')->getData(), $animal, $photoUploader);
            $this->handleGalleryPhotos($form->get('photosFiles')->getData(), $animal, $photoUploader);
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

            foreach ($animal->getPhotos() as $photo) {
                $photoUploader->remove($photo->getFilename());
            }

            $entityManager->remove($animal);
            $entityManager->flush();

            $this->addFlash('success', 'L\'animal a bien été supprimé.');
        } else {
            $this->addFlash('danger', 'Action non autorisée.');
        }

        return $this->redirectToRoute('app_animal_manage_index');
    }

    #[Route('/{id}/photos/{photo}/supprimer', name: 'delete_photo', methods: ['POST'], requirements: ['id' => '\d+', 'photo' => '\d+'])]
    public function deletePhoto(Request $request, Animal $animal, AnimalPhoto $photo, EntityManagerInterface $entityManager, AnimalPhotoUploader $photoUploader): Response
    {
        if ($photo->getAnimal() !== $animal) {
            throw $this->createNotFoundException('Cette photo n\'appartient pas à cet animal.');
        }

        if ($this->isCsrfTokenValid('delete-animal-photo-'.$photo->getId(), (string) $request->request->get('_token'))) {
            $photoUploader->remove($photo->getFilename());
            $animal->removePhoto($photo);
            $entityManager->flush();

            $this->addFlash('success', 'La photo a bien été supprimée.');
        } else {
            $this->addFlash('danger', 'Action non autorisée.');
        }

        return $this->redirectToRoute('app_animal_manage_edit', ['id' => $animal->getId()]);
    }

    private function handlePhoto(?UploadedFile $file, Animal $animal, AnimalPhotoUploader $uploader): void
    {
        if (null === $file) {
            return;
        }

        $uploader->remove($animal->getPhoto());
        $animal->setPhoto($uploader->upload($file));
    }

    /**
     * @param iterable<UploadedFile>|null $files
     */
    private function handleGalleryPhotos(?iterable $files, Animal $animal, AnimalPhotoUploader $uploader): void
    {
        if (null === $files) {
            return;
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $photo = new AnimalPhoto();
            $photo->setFilename($uploader->upload($file));
            $animal->addPhoto($photo);
        }
    }
}
