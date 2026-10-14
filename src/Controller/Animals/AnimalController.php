<?php

namespace App\Controller\Animals;

use App\Entity\Animal;
use App\Enum\AnimalStatus;
use App\Enum\Species;
use App\Repository\AnimalRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnimalController extends AbstractController
{
    #[Route('/animaux', name: 'app_animal_index', methods: ['GET'])]
    public function index(Request $request, AnimalRepository $animalRepository): Response
    {
        $statusValue = (string) $request->query->get('status', AnimalStatus::AVAILABLE->value);
        $speciesValue = (string) $request->query->get('species', '');

        $status = '' !== $statusValue ? AnimalStatus::tryFrom($statusValue) : null;
        $species = '' !== $speciesValue ? Species::tryFrom($speciesValue) : null;

        return $this->render('Animal/index.html.twig', [
            'animals' => $animalRepository->findByFilters($status, $species),
            'current_status' => $status,
            'current_species' => $species,
            'species_choices' => Species::cases(),
            'status_choices' => AnimalStatus::cases(),
        ]);
    }

    #[Route('/animaux/{slug}', name: 'app_animal_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Animal $animal): Response
    {
        return $this->render('Animal/show.html.twig', [
            'animal' => $animal,
        ]);
    }
}
