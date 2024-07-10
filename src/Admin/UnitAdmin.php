<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Unit;
use App\Traits\AdminTrait;
use InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

final class UnitAdmin extends AbstractAdmin
{
    use AdminTrait;

    /* Remove batch delete action from the list */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
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
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $actions = [
            'show' => [
                'template' => 'CRUD/list__action_show_custom.html.twig',
            ],
            'edit' => [
                'template' => 'CRUD/list__action_edit_custom.html.twig',
            ],
            // 'delete' => [
            //     'template' => 'CRUD/list__action_delete_custom.html.twig',
            // ],
        ];

        $list
            ->addIdentifier('name')
            // ->add('image')
            // ->add('logo')
            ->add('description')
            ->add('active')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'header_style' => 'width: 25%;',
                'actions' => $actions,
            ])
        ;
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        $editRoute = $this->isCurrentRoute('edit');

        if ($editRoute) {
            /** @var Unit $subject  */
            $subject = $this->getSubject();
            $unitName = $subject->getName();
        }

        $form
            ->with('First Column', [
                'label' => $editRoute ? $unitName : 'Add New Unit',
                'class' => 'col-md-6'
            ])
                ->add('name')
                // ->add('image')
                // ->add('logo')
                ->add('description')
                ->end()
            ->with('Active', [
                    'label' => 'Active Unit',
                    'class' => 'col-md-6'
                    ])
                ->add('active')
            ->end()
        ;
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Department', ['class' => 'col-md-6'])
                ->add('name')
                // ->add('image')
                // ->add('logo')
                ->add('description')
            ->end()
        ;
    }

    // MARK: - Configure Query
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        if (!$query instanceof ProxyQuery) {
            throw new InvalidArgumentException('Expected an instance of ProxyQuery');
        }

        $qb = $query->getQueryBuilder();
        $rootAlias = current($query->getQueryBuilder()->getRootAliases());

        /* Get switched unit */
        $unitId = $this->getSwitchedUnit();

        if ($unitId) {
            $qb
                ->where($rootAlias . '.id = :unitId')
                ->setParameter('unitId', $unitId)
            ;
        } else {
            $qb
                ->where($rootAlias . '.active = :active')
                ->setParameter('active', true)
            ;
        }

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }
}
