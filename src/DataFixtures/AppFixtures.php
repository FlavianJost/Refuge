<?php

namespace App\DataFixtures;

use App\Entity\AdoptionRequest;
use App\Entity\Animal;
use App\Entity\User;
use App\Enum\AdoptionRequestStatus;
use App\Enum\AnimalStatus;
use App\Enum\Species;
use App\Service\AnimalSlugger;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AnimalSlugger $animalSlugger,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createUser('admin@refuge.test', 'Alice', 'Martin', ['ROLE_ADMIN']);
        $volunteer = $this->createUser('benevole@refuge.test', 'Bruno', 'Petit', ['ROLE_VOLUNTEER']);
        $camille = $this->createUser('camille@refuge.test', 'Camille', 'Durand',['ROLE_USER']);
        $david = $this->createUser('david@refuge.test', 'David', 'Leroy', ['ROLE_USER']);
        $manager->persist($admin);
        $manager->persist($volunteer);
        $manager->persist($camille);
        $manager->persist($david);

        $animals = [
            ['Rex', Species::DOG, 'Berger allemand', 4, 'Très affectueux et joueur, il s\'entend bien avec les enfants.', AnimalStatus::AVAILABLE],
            ['Mimi', Species::CAT, 'Européen', 2, 'Calme et câline, elle adore les siestes au soleil.', AnimalStatus::AVAILABLE],
            ['Nala', Species::DOG, 'Border collie', 3, 'Énergique, elle a besoin de se dépenser chaque jour.', AnimalStatus::PENDING],
            ['Biscuit', Species::RABBIT, 'Nain', 1, 'Petit et sociable, parfait pour un premier animal.', AnimalStatus::AVAILABLE],
            ['Kiwi', Species::BIRD, 'Perruche', 5, 'Bavarde et colorée, elle réclame de l\'attention.', AnimalStatus::ADOPTED],
            ['Noisette', Species::RODENT, 'Cochon d\'Inde', 2, 'Douce et tranquille, facile à vivre.', AnimalStatus::AVAILABLE],
            ['Rex', Species::CAT, 'Européen', 1, 'Un second Rex, arrivé au refuge peu après le premier.', AnimalStatus::AVAILABLE],
        ];

        $entities = [];
        foreach ($animals as [$name, $species, $breed, $age, $description, $status]) {
            $animal = (new Animal())
                ->setName($name)
                ->setSpecies($species)
                ->setBreed($breed)
                ->setAge($age)
                ->setDescription($description)
                ->setStatus($status);

            $animal->setSlug($this->animalSlugger->generate($animal));

            $manager->persist($animal);
            $manager->flush();
            $entities[] = $animal;
        }

        $request1 = (new AdoptionRequest())
            ->setAnimal($entities[0])
            ->setUser($camille)
            ->setMessage('Bonjour, je souhaite adopter Rex. J\'ai un jardin clos.')
            ->setStatus(AdoptionRequestStatus::PENDING);

        $request2 = (new AdoptionRequest())
            ->setAnimal($entities[2])
            ->setUser($david)
            ->setMessage('Bonjour, nous avons déjà un chien et cherchons un compagnon pour lui.')
            ->setStatus(AdoptionRequestStatus::IN_PROGRESS)
            ->setProcessedBy($volunteer)
            ->touch();

        $manager->persist($request1);
        $manager->persist($request2);

        $manager->flush();
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(string $email, string $firstName, string $lastName, array $roles): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRoles($roles);

        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

        return $user;
    }
}
