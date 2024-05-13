<?php

declare(strict_types=1);

namespace App\Admin;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CurrencyExchangeAdmin extends TransactionAdmin
{
    protected $baseRoutePattern = 'currency_exchange';
    protected $baseRouteName = 'currency_exchange';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** AccountRepository $accountRepository */
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
            $transactionType = $subject->getTransactionTypeName();

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
                'label' => $mainAccount->getNameWithCurrencyBalance() . ' - Currency Exchange',
                'class' => 'col-md-5'
            ])
        ;

        if ($editRoute) {
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
                            ->where('tt.name = :currencyExchange')
                            ->setParameter('currencyExchange', 'Currency Exchange');
                    },
                ])
                // ->add('transactionType', EntityType::class, [
                //     'class' => TransactionType::class,
                //     'label' => 'Transaction Type',
                //     'query_builder' => function (EntityRepository $er) {
                //         return $er->createQueryBuilder('tt')
                //             ->where('tt.name = :currency_exchange')
                //             ->setParameter('currency_exchange', 'Currency exchange');
                //     },
                //     'attr' => [
                //         'style' => 'display: none;'
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
            ->add('date', DatePickerType::class, [
                'years' => range(1900, $now->format('Y')),
                'dp_max_date' => $now->format('c'),
                'required' => true,
                'format' => 'dd.MM.yyyy',
                'label' => 'Date'
            ])
            ->add('currency', TextType::class, [
                'label' => 'Account Currency',
                'data' => $accountCurrency,
                'disabled' => true,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            ->add('transferToAccount', EntityType::class, [
                'label' => 'Send to Account',
                'placeholder' => 'Select an Account',
                'class' => Account::class,
                'choice_label' => 'nameWithCurrencyBalance',
                'query_builder' => function (EntityRepository $er) use ($accountId, $accountCurrency, $unitId) {
                    return $er->createQueryBuilder('a')
                        ->where('a.unit = :unit')
                        ->andWhere('a.id <> :accountId')
                        ->andWhere('a.currency <> :currency')
                        ->andWhere('a.deactivated = false')
                        ->andWhere('at.name IN (:accountTypes)')
                        ->join('a.accountType', 'at')
                        ->orderBy('a.name', 'ASC')
                        ->setParameter('accountId', $accountId)
                        ->setParameter('accountTypes', ['Card Account', 'Cash Account'])
                        ->setParameter('currency', $accountCurrency)
                        ->setParameter('unit', $unitId)
                    ;
                },
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
                'required' => true,
                'attr' => [
                    'style' => 'text-align:end;',
                ]
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

    protected function preRemove(object $object): void
    {
        parent::preRemove($object);
    }
}
