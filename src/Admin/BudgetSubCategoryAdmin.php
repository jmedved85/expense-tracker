<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\BudgetMainCategory;
use App\Entity\BudgetCategory;
use App\Entity\InvoiceLine;
use App\Entity\PurchaseLine;
use App\Repository\InvoiceLineRepository;
use App\Repository\PurchaseLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class BudgetSubCategoryAdmin extends AbstractAdmin
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /* Remove Download button from bottom of the list */
    public function getExportFormats(): array
    {
        return [];
    }

    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('budgetMainCategory', null, [
                'label' => 'Main Category',
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => BudgetMainCategory::class,
                    'choice_label' => 'name',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('bmc')
                            ->orderBy('bmc.name', 'ASC');
                    },
                ],
            ])
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        /* TODO: add budget expenses per category / main category*/
        $list
            ->add('name')
            ->add('budgetMainCategory.name', null, [
                'label' => 'Main Category'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $form
            ->with('Budget Sub Category', ['class' => 'col-md-6'])
                ->add('name')
                ->add('budgetMainCategory', EntityType::class, [
                    'class' => BudgetMainCategory::class,
                    'choice_label' => 'name',
                    // 'choice_label' => $unitId ? 'name' : 'nameWithUnit',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('bmc')
                            ->orderBy('bmc.name', 'ASC');
                    },
                ])
            ->end()
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('name')
            ->add('budgetMainCategory.name', null, [
                'label' => 'Main Category'
            ])
        ;
    }

    // MARK: - PreRemove
    // protected function preRemove(object $object): void
    // {
    //     /** @var BudgetSubCategory $object */

    //     /** @var InvoiceLineRepository $invoiceLineRepository */
    //     $invoiceLineRepository = $this->entityManager->getRepository(InvoiceLine::class);
    //     /** @var PurchaseLineRepository $purchaseLineRepository */
    //     $purchaseLineRepository = $this->entityManager->getRepository(PurchaseLine::class);

    //     $invoiceLines = $object->getInvoiceLines()->toArray();
    //     $purchaseLines = $object->getPurchaseLines()->toArray();

    //     if ($invoiceLines) {
    //         foreach ($invoiceLines as $line) {
    //             $line->setBudgetCategory(null);

    //             $invoiceLineRepository->add($line, true);
    //         }
    //     }

    //     if ($purchaseLines) {
    //         foreach ($purchaseLines as $line) {
    //             $line->setBudgetCategory(null);

    //             $purchaseLineRepository->add($line, true);
    //         }
    //     }
    // }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }
}
