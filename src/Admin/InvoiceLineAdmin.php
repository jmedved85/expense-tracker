<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\BudgetSubCategory;
use App\Entity\InvoiceLine;
use App\Traits\AdminTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class InvoiceLineAdmin extends AbstractAdmin
{
    use AdminTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('netValue')
            ->add('vat')
            ->add('vatValue')
            ->add('lineTotal')
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('netValue')
            ->add('vat')
            ->add('vatValue')
            ->add('lineTotal')
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
        /** @var InvoiceLine|null $subject */
        $subject = $this->hasSubject() ? $this->getSubject() : null;

        $invoiceType = null;

        // $budgetMainCategory = $subject ? $subject->getBudgetMainCategory() : null;

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if (!$unitId) {
        //     $unitId = $subject->getInvoice()->getUnit()->getId();
        // }

        // if (isset($subject)) {
        //     $invoice = $subject ? $subject->getInvoice() : null;
        //     $invoiceUnit = $invoice ? $invoice->getUnit() : null;
        //     $unitId = $invoiceUnit ? $invoiceUnit->getId() : null;
        // }

        // $netValue = $subject->getNetValue();
        // $vat = $subject->getVat();
        // $vatValue = $subject->getVatValue();
        // $lineTotal = $subject->getLineTotal();

        $form
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Item Description',
            ])
            /* TODO: BothOrNoneValidator for categories && reset both categories on 'x' button click */
            // ->add('budgetMainCategory', EntityType::class, [
            //     'label' => 'Main Category',
            //     'class' => BudgetMainCategory::class,
            //     'required' => false,
            //     'placeholder' => 'Choose an option',
            //     'query_builder' => function (EntityRepository $er) use ($unitId) {
            //         if (isset($unitId)) {
            //             return $er->createQueryBuilder('bmc')
            //                 ->where('bmc.unit = :unit')
            //                 ->orderBy('bmc.name', 'ASC')
            //                 ->setParameter('unit', $unitId)
            //             ;
            //         } else {
            //             return $er->createQueryBuilder('bmc')
            //                 ->orderBy('bmc.name', 'ASC')
            //             ;
            //         }
            //     },
            // ])
            ->add('budgetSubCategory', EntityType::class, [
                'label' => 'Sub Category',
                'class' => BudgetSubCategory::class,
                'choice_label' => 'name',
                // 'choice_label' => $budgetMainCategory ? 'name' : 'nameWithMain',
                'required' => false,
                'placeholder' => 'Choose an option',
                // 'query_builder' =>
                //     function (EntityRepository $er) use ($budgetMainCategory, $unitId) {
                //         if ($budgetMainCategory) {
                //             $budgetSubCategories = $budgetMainCategory->getBudgetCategories()->toArray();

                //             return $er->createQueryBuilder('bc')
                //                 ->where('bc IN (:budgetSubCategories)')
                //                 ->orderBy('bc.name', 'ASC')
                //                 ->setParameter('budgetSubCategories', $budgetSubCategories)
                //             ;
                //         } else {
                //             if (isset($unitId)) {
                //                 return $er->createQueryBuilder('bc')
                //                     ->where('bc.unit = :unit')
                //                     ->orderBy('bc.name', 'ASC')
                //                     ->setParameter('unit', $unitId)
                //                 ;
                //             } else {
                //                 return $er->createQueryBuilder('bc')
                //                     ->orderBy('bc.name', 'ASC')
                //                 ;
                //             }
                //         }
                //     }
                // ,
            ])
            ->add('netValue', MoneyType::class, [
                'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                'currency' => false,
                'required' => false,
                // 'help' => "Insert value in 'X,XXX.XX' format",
                'attr' => [
                    'style' => 'text-align:end;',
                    // 'style' =>
                    //     ($netValue > 0 && $vat == null)
                    //     ? 'text-align: end; background-color: #f2f2f2; pointer-events: none;'
                    //     : 'text-align: end;',
                    // 'placeholder' => 'Insert Net Value',
                ]
            ])
            ->add('vat', PercentType::class, [
                'label' => 'VAT %',
                'required' => false,
                'attr' => [
                    'style' => 'text-align:end;',
                    // 'style' =>
                    //     ($lineTotal > 0 && $vat == null)
                    //     ? 'text-align: end; background-color: #f2f2f2; pointer-events: none;'
                    //     : 'text-align: end;',
                    // 'placeholder' => 'Insert VAT',
                ]
            ])
            ->add('vatValue', MoneyType::class, [
                'label' => 'VAT Value',
                'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                'currency' => false,
                'required' => false,
                // 'help' => "Insert value in 'X,XXX.XX' format",
                'attr' => [
                    'style' => 'text-align:end;',
                    // 'style' =>
                    //     ($lineTotal > 0 && $vatValue == null)
                    //     ? 'text-align: end; background-color: #f2f2f2; pointer-events: none;'
                    //     : 'text-align: end;',
                    // 'placeholder' => 'Insert VAT Value',
                ]
            ])
            ->add('lineTotal', MoneyType::class, [
                'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                'currency' => false,
                'required' => true,
                // 'help' => "Insert value in 'X,XXX.XX' format",
                'attr' => [
                    'style' => 'text-align:end;',
                    // 'style' =>
                    //     ($lineTotal > 0)
                    //     ? 'text-align: end; background-color: #f2f2f2; pointer-events: none;'
                    //     : 'text-align: end;',
                    // 'placeholder' => 'Insert Line Total',
                ]
            ])
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('netValue')
            ->add('vat')
            ->add('vatValue')
            ->add('lineTotal')
        ;
    }
}
