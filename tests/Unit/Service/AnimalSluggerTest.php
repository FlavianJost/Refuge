<?php

namespace App\Tests\Unit\Service;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
use App\Service\AnimalSlugger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AnimalSluggerTest extends TestCase
{
    public function testGeneratesSlugFromName(): void
    {
        $repository = $this->createMock(AnimalRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $slugger = new AnimalSlugger(new AsciiSlugger(), $repository);
        $animal = (new Animal())->setName('Éléphant bleu');

        self::assertSame('elephant-bleu', $slugger->generate($animal));
    }

    public function testFallsBackToAnimalWhenNameIsEmpty(): void
    {
        $repository = $this->createMock(AnimalRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $slugger = new AnimalSlugger(new AsciiSlugger(), $repository);

        self::assertSame('animal', $slugger->generate(new Animal()));
    }

    public function testAppendsSuffixOnConflict(): void
    {
        $existing = ($this->createStub(Animal::class));
        $existing->method('getId')->willReturn(42);

        $repository = $this->createMock(AnimalRepository::class);
        $repository->method('findOneBy')->willReturnCallback(
            static fn (array $criteria): ?Animal => 'rex' === $criteria['slug'] ? $existing : null,
        );

        $slugger = new AnimalSlugger(new AsciiSlugger(), $repository);
        $animal = (new Animal())->setName('Rex');

        self::assertSame('rex-2', $slugger->generate($animal));
    }

    public function testKeepsBaseSlugWhenConflictIsTheSameEntity(): void
    {
        $animal = (new Animal())->setName('Rex');
        $this->setId($animal, 7);

        $repository = $this->createMock(AnimalRepository::class);
        $repository->method('findOneBy')->willReturn($animal);

        $slugger = new AnimalSlugger(new AsciiSlugger(), $repository);

        self::assertSame('rex', $slugger->generate($animal));
    }

    private function setId(Animal $animal, int $id): void
    {
        (new \ReflectionProperty(Animal::class, 'id'))->setValue($animal, $id);
    }
}
