<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class UserUnitAdmin extends AbstractAdmin
{
    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('memberType')
            ->add('status')
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('memberType')
            ->add('status')
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
        $form
            ->add('memberType')
            ->add('status')
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('memberType')
            ->add('status')
        ;
    }
}
