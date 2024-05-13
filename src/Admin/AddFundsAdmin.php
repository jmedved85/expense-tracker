<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Account;
use App\Entity\AccountType;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use App\Entity\TransactionType;
use App\Entity\Unit;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class AddFundsAdmin extends TransactionAdmin
{
    protected $baseRoutePattern = 'add_funds';
    protected $baseRouteName = 'add_funds';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var UnitRepository $unitRepository */
        $unitRepository = $this->entityManager->getRepository(Unit::class);

        $now = new DateTime();

        // /* Get unit */
        // $unitId = $this->getUnitId();

        // $unit = null;

        // if ($unitId) {
        //     $unit = $unitRepository->findOneBy(['id' => $unitId]);
        // }

        $bankAccountType = AccountType::BANK;

        $form
            ->with('Add Funds', [
                // 'label' => isset($unit) ? 'Add Funds to ' . $unit->getName() : '',
                'class' => 'col-md-4'
            ])
                ->add('mainAccount', EntityType::class, [
                    'label' => 'To Account',
                    'class' => Account::class,
                    'choice_label' => 'nameWithCurrencyBalance',
                    'placeholder' => 'Select an Account',
                    // 'query_builder' =>
                    // function (EntityRepository $er) use ($bankAccountType, $unit) {
                    //     if ($unit) {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.accountType = :bankAccountType')
                    //             ->andWhere('a.deactivated = false')
                    //             ->andWhere('a.unit = :unit')
                    //             ->setParameters([
                    //                 'bankAccountType' => $bankAccountType,
                    //                 'unit' => $unit->getId()
                    //             ])
                    //             ->orderBy('a.currency', 'ASC')
                    //             ->addOrderBy('a.name', 'ASC')
                    //         ;
                    //     } else {
                    //         return $er->createQueryBuilder('a')
                    //             ->andWhere('a.accountType = :bankAccountType')
                    //             ->andWhere('a.deactivated = false')
                    //             ->setParameters([
                    //                 'bankAccountType' => $bankAccountType,
                    //             ])
                    //             ->orderBy('a.currency', 'ASC')
                    //             ->addOrderBy('a.name', 'ASC')
                    //         ;
                    //     }
                    // },
                ])
                ->add('date', DatePickerType::class, [
                    'label' => 'Date',
                    'years' => range(1900, $now->format('Y')),
                    'dp_max_date' => $now->format('c'),
                    'format' => 'dd.MM.yyyy',
                    // 'data' => $now
                ])
                ->add('transactionType', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => TransactionType::NAMES,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('tt')
                            ->where('tt.name = :fundsTransfer')
                            ->setParameter('fundsTransfer', 'Funds Transfer');
                    },
                ])
                // ->add('transactionType', EntityType::class, [
                //     'class' => TransactionType::class,
                //     'label' => 'Transaction Type',
                //     'query_builder' => function (EntityRepository $er) {
                //         return $er->createQueryBuilder('tt')
                //             ->where('tt.name = :fundsTransfer')
                //             ->setParameter('fundsTransfer', 'Funds transfer');
                //     },
                // ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'label' => 'Description',
                ])
            ->end()
            ->with('Payments', [
                'class' => 'col-md-4 mt-5'
            ])
                ->add('amount', MoneyType::class, [
                    'label' => 'Amount',
                    // NOTE: Currency digit grouping; it is needed for validation to pass
                    'grouping' => true,
                    'required' => true,
                ])
                ->add('bankFeeAmount', MoneyType::class, [
                    'label' => 'Bank Fee',
                    // NOTE: Currency digit grouping; it is needed for validation to pass
                    'grouping' => true,
                    'required' => false,
                ])
                ->add('bankFeeNotApplicable', null, [
                    'label' => 'Bank Fee Not Applicable',
                    'required' => false,
                ])
            ->end()
            ->with('File Uploads', [
                'class' => 'col-md-4 mt-5'
            ])
                // ->add('file', CollectionType::class, [
                //     'label' => 'Upload file(s)',
                //     'required' => false,
                //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF'
                // ], [
                //     'edit' => 'inline',
                //     'inline' => 'form',
                //     'sortable'  => 'position',
                // ])
            ->end()
        ;
    }

    protected function prePersist(object $object): void
    {
        parent::prePersist($object);
    }

    protected function preUpdate(object $object): void
    {
        parent::preUpdate($object);
    }
}
