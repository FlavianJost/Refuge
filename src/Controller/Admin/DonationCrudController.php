<?php

namespace App\Controller\Admin;

use App\Entity\Donation;
use App\Enum\DonationStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DonationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Donation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Don')
            ->setEntityLabelInPlural('Dons')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield MoneyField::new('amount', 'Montant')->setStoredAsCents()->setCurrency('EUR');
        yield TextField::new('donorName', 'Donateur');
        yield EmailField::new('donorEmail', 'Email');
        yield ChoiceField::new('status', 'Statut')
            ->renderAsBadges([
                DonationStatus::PAID->name => 'success',
                DonationStatus::PENDING->name => 'warning',
                DonationStatus::FAILED->name => 'danger',
            ]);
        yield TextareaField::new('message', 'Message')->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Reçu le')->hideOnForm()->setFormat('dd/MM/yyyy HH:mm');
    }
}
