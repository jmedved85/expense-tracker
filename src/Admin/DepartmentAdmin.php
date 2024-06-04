<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Department;
use App\Traits\AdminTrait;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class DepartmentAdmin extends AbstractAdmin
{
    use AdminTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /* Remove batch delete action from the list */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    // /* Remove Download button from bottom of the list */
    // public function getExportFormats(): array
    // {
    //     return [];
    // }

    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $filter
            ->add('name', null, [
                'advanced_filter' => false,
            ])
            ->add('unit', null, [
                'advanced_filter' => false,
            ])
        ;

        // /* If unit is not selected, unit selection is available */
        // if (!$unitId) {
        //     $filter
        //         ->add('unit', null, [
        //             'label' => 'Unit',
        //             'show_filter' => true,
        //             'field_type' => EntityType::class,
        //             'field_options' => [
        //                 'class' => Unit::class,
        //                 'choice_label' => 'name',
        //                 'query_builder' => function (EntityRepository $er) {
        //                     return $er->createQueryBuilder('s')
        //                         ->andWhere('s.active = :active')
        //                         ->orderBy('s.name', 'ASC')
        //                         ->setParameter('active', true)
        //                     ;
        //                 },
        //             ],
        //         ])
        //     ;
        // }
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $actions = [
            'show' => [
                'template' => 'CRUD/list__action_show_custom.html.twig',
            ],
            'edit' => [
                'template' => 'CRUD/list__action_edit_custom.html.twig',
            ],
            'delete' => [
                'template' => 'CRUD/list__action_delete_custom.html.twig',
            ],
        ];

        $list
            ->addIdentifier('name')
            ->add('unit')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'header_style' => 'width: 25%;',
                'actions' => $actions,
            ])
        ;

        // if (!$unitId) {
        //     $list
        //         ->add('unit.name', null, [
        //             'label' => 'Unit'
        //         ])
        //     ;
        // }
    }

    // MARK: - Form Fields
    protected function configureFormFields(FormMapper $form): void
    {
        // /* Get unit */
        // $unitId = $this->getUnitId();

        $form
            ->with('Department', ['class' => 'col-md-6'])
                ->add('name')
                ->add('unit')
            ->end()
        ;

        // if (!$unitId) {
        //     $form
        //         ->add('unit', EntityType::class, [
        //             'label' => 'Unit',
        //             'class' => Unit::class,
        //             'choice_label' => 'name',
        //             'placeholder' => 'Choose an option',
        //             'query_builder' => function (EntityRepository $er) {
        //                 return $er->createQueryBuilder('s')
        //                     ->andWhere('s.active = :active')
        //                     ->orderBy('s.name', 'ASC')
        //                     ->setParameter('active', true)
        //                 ;
        //             },
        //         ])
        //     ;
        // }
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Department', ['class' => 'col-md-6'])
                ->add('name')
                ->add('unit.name', null, [
                    'label' => 'Unit'
                ])
            ->end()
        ;
    }

    // MARK: - PrePersist
    protected function prePersist(object $department): void
    {
        /** @var Department $department */

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $unitRepository = $this->entityManager->getRepository(Unit::class);
        //     $unit = $unitRepository->findOneBy(['id' => $unitId]);

        //     /* Persist created data */
        //     $department->setUnit($unit);
        // }
    }

    // MARK: - Configure Query
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        if (!$query instanceof ProxyQuery) {
            throw new InvalidArgumentException('Expected an instance of ProxyQuery');
        }

        // $qb = $query->getQueryBuilder();
        // $rootAlias = current($query->getQueryBuilder()->getRootAliases());

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // if ($unitId) {
        //     $qb
        //         ->where($rootAlias . '.unit = :unitId')
        //         ->setParameter('unitId', $unitId)
        //     ;
        // } else {
        //     $qb
        //         ->join($rootAlias . '.unit', 's')
        //         ->andWhere('s.active = :active')
        //         ->setParameter('active', true)
        //     ;
        // }

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }
}
