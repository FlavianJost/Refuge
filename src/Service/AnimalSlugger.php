<?php

namespace App\Service;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

class AnimalSlugger
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly AnimalRepository $animalRepository,
    ) {
    }

    public function generate(Animal $animal): string
    {
        $base = $this->slugger->slug($animal->getName() ?? '')->lower()->toString();

        if ('' === $base) {
            $base = 'animal';
        }

        $slug = $base;
        $suffix = 2;

        while (null !== ($existing = $this->animalRepository->findOneBy(['slug' => $slug])) && $existing->getId() !== $animal->getId()) {
            $slug = sprintf('%s-%d', $base, $suffix);
            ++$suffix;
        }

        return $slug;
    }
}
