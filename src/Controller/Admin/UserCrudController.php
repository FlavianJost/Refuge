<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['email', 'firstName', 'lastName']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('email', 'Adresse email');
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Bénévole' => 'ROLE_VOLUNTEER',
                'Utilisateur' => 'ROLE_USER',
            ])
            ->allowMultipleChoices()
            ->renderExpanded();
        yield DateTimeField::new('createdAt', 'Inscrit le')->hideOnForm()->setFormat('dd/MM/yyyy HH:mm');
        yield DateTimeField::new('lastLoginAt', 'Dernière connexion')->hideOnForm()->setFormat('dd/MM/yyyy HH:mm');
    }
}
