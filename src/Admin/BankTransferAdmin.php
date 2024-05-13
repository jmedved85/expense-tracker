<?php

declare(strict_types=1);

namespace App\Admin;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class BankTransferAdmin extends TransactionAdmin
{
    protected $baseRoutePattern = 'bank_transfer';
    protected $baseRouteName = 'bank_transfer';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);

        $now = new DateTime();

        $editRoute = $this->isCurrentRoute('edit');

        /* TODO: simplify the accountData session operation, put in separate service */

        if ($editRoute) {
            /** @var Transaction $subject */
            $subject = $this->getSubject();

            $unitId = $subject->getUnit()->getId();
            $mainAccount = $subject->getTransferFromAccount();
            $accountId = $mainAccount->getId();
            $accountType = $mainAccount->getAccountType();
            $accountCurrency = $mainAccount->getCurrency();

            $accountData = [];
            $accountData['accountId'] = $accountId;
            $accountData['accountCurrency'] = $accountCurrency;
            $accountData['accountType'] = $accountType;
            $accountData['unitId'] = $unitId;

            $this->session()->set('accountData', $accountData);
        } else {
            $request = $this->getRequest();
            $accountData = $request->query->all();

            if (!isset($accountData['accountId'])) {
                $accountData = $this->session()->get('accountData');
            } else {
                $this->session()->set('accountData', $accountData);
            }

            $accountId = $accountData['accountId'] ?? null;
            $mainAccount = $accountRepository->findOneBy(['id' => $accountId]) ?? null;
            $accountCurrency = $accountData['accountCurrency'] ?? null;
            $accountType = $accountData['accountType'] ?? null;
            $unitId = $accountData['unitId'] ?? null;
        }

        $form
            ->with('accountName', [
                'label' => $mainAccount->getNameWithCurrencyBalance() . ' - Bank Transfer',
                'class' => 'col-md-5'
            ])
        ;

        if ($editRoute) {
            $transactionType = $subject->getTransactionTypeName();

            $form
                ->add('transactionNumber', TextType::class, [
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('InfoTextType', null, [
                    'label' => 'Transaction Type',
                    'data' => ucwords($transactionType),
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'disabled' => true,
                        'readonly' => true,
                    ],
                ])
            ;
        } else {
            $form
                ->add('transactionType', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => TransactionType::NAMES,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('tt')
                            ->where('tt.name = :bankTransfer')
                            ->setParameter('bankTransfer', 'Bank Transfer');
                    },
                    'attr' => [
                        'style' => 'display: none;',
                    ],
                ])
                // ->add('transactionType', EntityType::class, [
                //     'class' => TransactionType::class,
                //     'label' => 'Transaction Type',
                //     'query_builder' => function (EntityRepository $er) {
                //         return $er->createQueryBuilder('tt')
                //             ->where('tt.name = :bankTransfer')
                //             ->setParameter('bankTransfer', 'Bank transfer')
                //         ;
                //     },
                //     'attr' => [
                //         'style' => 'display: none;',
                //     ],
                // ])
                ->add('infoText', null, [
                    'label' => 'Account Type',
                    'data' => $accountType,
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'disabled' => true,
                        'readonly' => true,
                    ],
                ])
            ;
        }

        $form
            ->add('currency', TextType::class, [
                'label' => 'Account Currency',
                'data' => $accountCurrency,
                'disabled' => true,
            ])
            ->add('date', DatePickerType::class, [
                'years' => range(1900, $now->format('Y')),
                'dp_max_date' => $now->format('c'),
                'required' => true,
                'format' => 'dd.MM.yyyy',
                'label' => 'Transfer Date'
            ])
            ->add('transferToAccount', EntityType::class, [
                'label' => 'Transfer to Account',
                'placeholder' => 'Select an Account',
                'class' => Account::class,
                'choice_label' => 'nameWithCurrencyBalance',
                'query_builder' => function (EntityRepository $er) use ($accountId, $unitId) {
                    return $er->createQueryBuilder('a')
                        ->where('a.unit = :unit')
                        ->andWhere('a.id <> :accountId')
                        ->andWhere('a.deactivated = false')
                        ->andWhere('at.name IN (:accountTypes)')
                        ->join('a.accountType', 'at')
                        ->orderBy('a.name', 'ASC')
                        ->setParameter('accountId', $accountId)
                        ->setParameter('accountTypes', ['Card Account', 'Bank Account'])
                        ->setParameter('unit', $unitId)
                    ;
                },
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                // NOTE: Currency digit grouping; it is needed for validation to pass
                'grouping' => true,
                'currency' => $accountCurrency,
                'required' => true,
                'attr' => [
                    'style' => 'text-align:end;',
                ]
            ])
            ->add('newValue', MoneyType::class, [
                'label' => 'New Value',
                // NOTE: Currency digit grouping; it is needed for validation to pass
                'grouping' => true,
                'attr' => [
                    'style' => 'text-align:end;',
                ],
                'required' => false,
            ])
            ->end()
            ->with('File Uploads', [
                'class' => 'col-md-5 mt-5'
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

    protected function preRemove(object $object): void
    {
        parent::preRemove($object);
    }
}
