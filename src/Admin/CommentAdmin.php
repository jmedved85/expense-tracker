<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Invoice;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CommentAdmin extends AbstractAdmin
{
    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('datetime')
            ->add('message')
            ->add('invoice.id', null, [
                'label' => 'Invoice',
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Invoice::class,
                    'choice_label' => 'id',
                ],
            ])
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('message')
            ->add('userDateTime', null, [
                'label' => 'Added'
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    // 'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        $editRoute = $this->isCurrentRoute('edit');

        $form
            ->add('message', TextareaType::class, [
                'label' => false,
                'disabled' => false,
                'attr' => [
                    'style' => 'height:8rem;',
                    'oninput' => 'this.style.height = ""; this.style.height = this.scrollHeight + "px"'
                ]
            ])
            ->add('userDateTime', TextType::class, [
                'label' => false,
                'disabled' => true,
                'attr' => [
                    'style' => !$editRoute ? 'display:none;' : 'background-color:transparent; border:0;'
                ]
            ])
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Comment', ['class' => 'col-md-6'])
                ->add('message')
                ->add('userDateTime', null, [
                    'label' => 'Added'
                ])
            ->end()
        ;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'datetime';
    }
}
