<?php

namespace App\DataFixtures;

use App\Entity\AdoptionRequest;
use App\Entity\AdoptionRequestEvent;
use App\Entity\Animal;
use App\Entity\AnimalPhoto;
use App\Entity\Conversation;
use App\Entity\LoginRecord;
use App\Entity\MedicalRecord;
use App\Entity\Message;
use App\Entity\User;
use App\Entity\VisitAppointment;
use App\Enum\AdoptionRequestStatus;
use App\Enum\AnimalStatus;
use App\Enum\HousingType;
use App\Enum\MedicalRecordCategory;
use App\Enum\Species;
use App\Enum\VisitAppointmentStatus;
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
        $now = new \DateTimeImmutable();

        $admin = $this->createUser('admin@refuge.test', 'Alice', 'Martin', ['ROLE_ADMIN']);
        $volunteer = $this->createUser('benevole@refuge.test', 'Bruno', 'Petit', ['ROLE_VOLUNTEER']);
        $camille = $this->createUser('camille@refuge.test', 'Camille', 'Durand', ['ROLE_USER']);
        $david = $this->createUser('david@refuge.test', 'David', 'Leroy', ['ROLE_USER']);

        // Statut « en ligne » : l'équipe et Camille sont vus à l'instant, David il y a 10 min (hors ligne).
        $admin->setLastSeenAt($now->modify('-2 minutes'));
        $volunteer->setLastSeenAt($now);
        $camille->setLastSeenAt($now);

        // Avatar de profil : dépose ton fichier dans public/uploads/users/ et renseigne son nom ici.
        // $camille->setPhoto('camille.jpg');

        $manager->persist($admin);
        $manager->persist($volunteer);
        $manager->persist($camille);
        $manager->persist($david);

        // Historique de connexions (section « Sécurité » du profil de Camille).
        for ($i = 0; $i < 3; ++$i) {
            $manager->persist(
                (new LoginRecord())
                    ->setUser($camille)
                    ->setIpAddress('192.168.1.'.random_int(2, 254))
            );
        }

        $manager->flush();

        // [nom, espèce, race, âge, description, statut, vues, photo de couverture, photos de galerie]
        // Renseigne les noms de fichiers que tu déposes dans public/uploads/animals/.
        $animals = [
            ['Rex', Species::DOG, 'Berger allemand', 4, 'Très affectueux et joueur, il s\'entend bien avec les enfants.', AnimalStatus::AVAILABLE, 128, null, []],
            ['Mimi', Species::CAT, 'Européen', 2, 'Calme et câline, elle adore les siestes au soleil.', AnimalStatus::AVAILABLE, 74, null, []],
            ['Nala', Species::DOG, 'Border collie', 3, 'Énergique, elle a besoin de se dépenser chaque jour.', AnimalStatus::PENDING, 51, null, []],
            ['Biscuit', Species::RABBIT, 'Nain', 1, 'Petit et sociable, parfait pour un premier animal.', AnimalStatus::AVAILABLE, 22, null, []],
            ['Kiwi', Species::BIRD, 'Perruche', 5, 'Bavarde et colorée, elle réclame de l\'attention.', AnimalStatus::ADOPTED, 39, null, []],
            ['Noisette', Species::RODENT, 'Cochon d\'Inde', 2, 'Douce et tranquille, facile à vivre.', AnimalStatus::AVAILABLE, 17, null, []],
            ['Rex', Species::CAT, 'Européen', 1, 'Un second Rex, arrivé au refuge peu après le premier.', AnimalStatus::AVAILABLE, 9, null, []],
        ];

        $entities = [];
        foreach ($animals as [$name, $species, $breed, $age, $description, $status, $views, $cover, $gallery]) {
            $animal = (new Animal())
                ->setName($name)
                ->setSpecies($species)
                ->setBreed($breed)
                ->setAge($age)
                ->setDescription($description)
                ->setStatus($status);

            for ($i = 0; $i < $views; ++$i) {
                $animal->incrementViews();
            }

            $animal->setSlug($this->animalSlugger->generate($animal));

            if (null !== $cover) {
                $animal->setPhoto($cover);
            }

            foreach ($gallery as $filename) {
                $animal->addPhoto((new AnimalPhoto())->setFilename($filename));
            }

            $manager->persist($animal);
            $manager->flush();
            $entities[] = $animal;
        }

        // Dossier médical (visible uniquement par l'équipe).
        $medicalRecords = [
            [$entities[0], MedicalRecordCategory::VACCINATION, 'Vaccin rage', 'Rappel annuel effectué.', 'Dr Dupont', $now->modify('-1 year')],
            [$entities[0], MedicalRecordCategory::VET_VISIT, 'Contrôle général', 'Animal en bonne santé, rien à signaler.', 'Dr Dupont', $now->modify('-2 months')],
            [$entities[0], MedicalRecordCategory::TREATMENT, 'Traitement antiparasitaire', 'Traitement mensuel contre les puces et les tiques.', null, $now->modify('-3 weeks')],
            [$entities[1], MedicalRecordCategory::SURGERY, 'Stérilisation', 'Intervention réalisée sans complication.', 'Clinique des Lilas', $now->modify('-6 months')],
            [$entities[3], MedicalRecordCategory::CARE, 'Coupe des griffes', null, null, $now->modify('-1 week')],
        ];

        foreach ($medicalRecords as [$animal, $category, $title, $description, $veterinarian, $date]) {
            $record = (new MedicalRecord())
                ->setAnimal($animal)
                ->setCategory($category)
                ->setTitle($title)
                ->setDate($date);

            if (null !== $description) {
                $record->setDescription($description);
            }

            if (null !== $veterinarian) {
                $record->setVeterinarian($veterinarian);
            }

            $animal->addMedicalRecord($record);
            $manager->persist($record);
        }

        // Demandes d'adoption (avec questionnaire pré-adoption).
        $request1 = (new AdoptionRequest())
            ->setAnimal($entities[0])
            ->setUser($camille)
            ->setMessage('Bonjour, je souhaite adopter Rex. J\'ai un jardin clos.')
            ->setHousing(HousingType::HOUSE)
            ->setHasGarden(true)
            ->setHasOtherPets(true)
            ->setHasChildren(false)
            ->setExperience('J\'ai eu un chien pendant douze ans.')
            ->setStatus(AdoptionRequestStatus::PENDING);

        $request2 = (new AdoptionRequest())
            ->setAnimal($entities[2])
            ->setUser($david)
            ->setMessage('Bonjour, nous avons déjà un chien et cherchons un compagnon pour lui.')
            ->setHousing(HousingType::APARTMENT)
            ->setHasGarden(false)
            ->setHasOtherPets(true)
            ->setHasChildren(true)
            ->setExperience('Première adoption pour notre famille.')
            ->setStatus(AdoptionRequestStatus::IN_PROGRESS)
            ->setProcessedBy($volunteer)
            ->touch();

        $manager->persist($request1);
        $manager->persist($request2);

        // Parcours de la demande (timeline).
        $request1->addEvent(
            (new AdoptionRequestEvent())
                ->setStatus(AdoptionRequestStatus::PENDING)
                ->setAuthor($camille)
                ->setNote('Demande déposée.')
        );

        $request2->addEvent(
            (new AdoptionRequestEvent())
                ->setStatus(AdoptionRequestStatus::PENDING)
                ->setAuthor($david)
        );
        $request2->addEvent(
            (new AdoptionRequestEvent())
                ->setStatus(AdoptionRequestStatus::IN_PROGRESS)
                ->setAuthor($volunteer)
                ->setNote('Nous avons pris contact par téléphone.')
        );

        // Rendez-vous de visite : un en attente, un confirmé.
        $request1->addAppointment(
            (new VisitAppointment())
                ->setProposedAt($now->modify('+3 days')->setTime(14, 30))
                ->setNote('Je suis disponible en début d\'après-midi.')
                ->setStatus(VisitAppointmentStatus::PENDING)
        );

        $request2->addAppointment(
            (new VisitAppointment())
                ->setProposedAt($now->modify('+2 days')->setTime(10, 0))
                ->setProcessedBy($volunteer)
                ->setStatus(VisitAppointmentStatus::CONFIRMED)
                ->setResponse('Parfait, rendez-vous confirmé à 10h au refuge.')
                ->touch()
        );

        // Messagerie : une conversation répondue et une en attente.
        $conversation = new Conversation($camille);
        $conversation->addMessage(
            (new Message())
                ->setAuthor($camille)
                ->setIsStaff(false)
                ->setBody('Bonjour, Rex est-il disponible pour une visite ce week-end ?')
        );
        $conversation->addMessage(
            (new Message())
                ->setAuthor($volunteer)
                ->setIsStaff(true)
                ->setBody('Bonjour Camille, oui, nous sommes ouverts samedi de 10h à 17h.')
        );
        $conversation->addMessage(
            (new Message())
                ->setAuthor($camille)
                ->setIsStaff(false)
                ->setBody('Super, je passerai samedi matin. Merci !')
        );
        $conversation->setLastMessageAt($now);
        $conversation->setUnreadForUser(0);
        $conversation->setUnreadForStaff(1);
        $manager->persist($conversation);

        $conversation2 = new Conversation($david);
        $conversation2->addMessage(
            (new Message())
                ->setAuthor($david)
                ->setIsStaff(false)
                ->setBody('Bonjour, où en est ma demande pour Nala ?')
        );
        $conversation2->setLastMessageAt($now->modify('-2 hours'));
        $conversation2->setUnreadForUser(0);
        $conversation2->setUnreadForStaff(1);
        $manager->persist($conversation2);

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
