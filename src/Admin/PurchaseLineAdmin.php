<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\BudgetSubCategory;
use App\Traits\AdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class PurchaseLineAdmin extends AbstractAdmin
{
    // use AdminTrait;

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
//            ->add('amountNet')
            ->add('vat')
            ->add('vatValue')
//            ->add('amountTotal')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
//            ->add('amountNet')
            ->add('vat')
            ->add('vatValue')
//            ->add('amountTotal')
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

    protected function configureFormFields(FormMapper $form): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $subject = $this->getSubject();

        // if (!$unitId) {
        //     $unitId = $subject->getPurchase()->getUnit()->getId();
        // }

        $form
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Item Description',
            ])
            ->add('budgetSubCategory', EntityType::class, [
                'class' => BudgetSubCategory::class,
                'choice_label' => 'name',
                'label' => 'Sub Category',
                'required' => false,
                'placeholder' => 'Choose an option',
                // 'query_builder' =>
                //     function (EntityRepository $er) use ($unitId) {
                //         return $er->createQueryBuilder('bc')
                //             ->where('bc.unit = :unit')
                //             ->orderBy('bc.name', 'ASC')
                //             ->setParameter('unit', $unitId)
                //         ;
                //     }
            ])
            /* TODO: fix not able to save number with comma */
            ->add('netValue', MoneyType::class, [
                'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                'currency' => false,
                'required' => false,
                // 'help' => "Insert value in 'X,XXX.XX' format",
                'attr' => [
                    'style' => 'text-align:end;',
                ]
            ])
            ->add('vat', PercentType::class, [
                'label' => 'VAT %',
                'required' => false,
                'attr' => [
                    'style' => 'text-align:end;',
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
                ]
            ])
            ->add('lineTotal', MoneyType::class, [
                'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                'currency' => false,
                // 'help' => "Insert value in 'X,XXX.XX' format",
                'attr' => [
                    'style' => 'text-align:end;',
                ]
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('amountNet')
            ->add('vat')
            ->add('vatValue')
            ->add('amountTotal')
        ;
    }
}
