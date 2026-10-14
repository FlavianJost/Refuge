<?php

namespace App\Controller\Admin;

use App\Entity\AdoptionRequest;
use App\Entity\User;
use App\Enum\AdoptionRequestStatus;
use App\Service\AdoptionRequestProcessor;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class AdoptionRequestCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdoptionRequestProcessor $adoptionRequestProcessor,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return AdoptionRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Demande d\'adoption')
            ->setEntityLabelInPlural('Demandes d\'adoption')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield AssociationField::new('animal', 'Animal');
        yield AssociationField::new('user', 'Demandeur');
        yield ChoiceField::new('status', 'Statut')
            ->renderAsBadges([
                AdoptionRequestStatus::PENDING->name => 'info',
                AdoptionRequestStatus::IN_PROGRESS->name => 'warning',
                AdoptionRequestStatus::ACCEPTED->name => 'success',
                AdoptionRequestStatus::REJECTED->name => 'danger',
            ]);
        yield TextareaField::new('message', 'Message')->hideOnIndex();
        yield AssociationField::new('processedBy', 'Traité par')->setDisabled();
        yield DateTimeField::new('createdAt', 'Créée le')->hideOnForm()->setFormat('dd/MM/yyyy HH:mm');
        yield DateTimeField::new('updatedAt', 'Mise à jour le')->hideOnForm()->setFormat('dd/MM/yyyy HH:mm');
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof AdoptionRequest) {
            $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
            $oldStatus = $originalData['status'] ?? null;
            $newStatus = $entityInstance->getStatus();
            $processedBy = $this->getUser();

            if ($oldStatus instanceof AdoptionRequestStatus
                && $oldStatus !== $newStatus
                && $processedBy instanceof User
            ) {
                $this->adoptionRequestProcessor->changeStatus($entityInstance, $newStatus, $processedBy);

                return;
            }

            $entityInstance->touch();
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
