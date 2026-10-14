<?php

namespace App\Controller\Animals;

use App\Entity\Animal;
use App\Entity\MedicalRecord;
use App\Form\Animal\MedicalRecordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestion/animaux/{id}/medical', name: 'app_medical_manage_', requirements: ['id' => '\d+'])]
#[IsGranted('ROLE_VOLUNTEER')]
class MedicalRecordManageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, Animal $animal, EntityManagerInterface $entityManager): Response
    {
        $record = new MedicalRecord();
        $form = $this->createForm(MedicalRecordType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animal->addMedicalRecord($record);
            $entityManager->persist($record);
            $entityManager->flush();

            $this->addFlash('success', 'L\'acte médical a bien été enregistré.');

            return $this->redirectToRoute('app_medical_manage_index', ['id' => $animal->getId()]);
        }

        return $this->render('Medical/index.html.twig', [
            'animal' => $animal,
            'records' => $animal->getMedicalRecords(),
            'form' => $form,
        ]);
    }

    #[Route('/{record}/modifier', name: 'edit', methods: ['GET', 'POST'], requirements: ['record' => '\d+'])]
    public function edit(Request $request, Animal $animal, MedicalRecord $record, EntityManagerInterface $entityManager): Response
    {
        if ($record->getAnimal() !== $animal) {
            throw $this->createNotFoundException('Cet acte médical n\'appartient pas à cet animal.');
        }

        $form = $this->createForm(MedicalRecordType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'acte médical a bien été mis à jour.');

            return $this->redirectToRoute('app_medical_manage_index', ['id' => $animal->getId()]);
        }

        return $this->render('Medical/edit.html.twig', [
            'animal' => $animal,
            'record' => $record,
            'form' => $form,
        ]);
    }

    #[Route('/{record}/supprimer', name: 'delete', methods: ['POST'], requirements: ['record' => '\d+'])]
    public function delete(Request $request, Animal $animal, MedicalRecord $record, EntityManagerInterface $entityManager): Response
    {
        if ($record->getAnimal() !== $animal) {
            throw $this->createNotFoundException('Cet acte médical n\'appartient pas à cet animal.');
        }

        if ($this->isCsrfTokenValid('delete-medical-'.$record->getId(), (string) $request->request->get('_token'))) {
            $animal->removeMedicalRecord($record);
            $entityManager->flush();

            $this->addFlash('success', 'L\'acte médical a bien été supprimé.');
        } else {
            $this->addFlash('danger', 'Action non autorisée.');
        }

        return $this->redirectToRoute('app_medical_manage_index', ['id' => $animal->getId()]);
    }
}
