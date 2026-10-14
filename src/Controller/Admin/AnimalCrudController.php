<?php

namespace App\Controller\Admin;

use App\Entity\Animal;
use App\Enum\AnimalStatus;
use App\Enum\Species;
use App\Service\AnimalSlugger;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class AnimalCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AnimalSlugger $animalSlugger,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Animal::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Animal')
            ->setEntityLabelInPlural('Animaux')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['name', 'breed', 'description']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Nom'))
            ->add(ChoiceFilter::new('species', 'Espèce')->setTranslatableChoices(Species::cases()))
            ->add(ChoiceFilter::new('status', 'Statut')->setTranslatableChoices(AnimalStatus::cases()));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name', 'Nom');
        yield ChoiceField::new('species', 'Espèce');
        yield TextField::new('breed', 'Race')->hideOnIndex();
        yield IntegerField::new('age', 'Âge')->setHelp('En années.');
        yield ChoiceField::new('status', 'Statut')->renderAsBadges([
            AnimalStatus::AVAILABLE->name => 'success',
            AnimalStatus::PENDING->name => 'warning',
            AnimalStatus::ADOPTED->name => 'secondary',
        ]);
        yield TextareaField::new('description', 'Description')->hideOnIndex();
        yield ImageField::new('photo', 'Photo')
            ->setBasePath('/uploads/animals')
            ->hideOnForm()
            ->setEmptyData('');
        yield TextField::new('slug', 'Slug')->hideOnForm()->setHelp('Généré automatiquement à partir du nom.');
        yield IntegerField::new('views', 'Vues')->hideOnForm()->setHelp('Nombre de consultations de la fiche.');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm()->setFormat('dd/MM/yyyy HH:mm');
        yield DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnForm()->setFormat('dd/MM/yyyy HH:mm');
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Animal) {
            $entityInstance->setSlug($this->animalSlugger->generate($entityInstance));
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Animal) {
            $entityInstance->setSlug($this->animalSlugger->generate($entityInstance));
            $entityInstance->touch();
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
