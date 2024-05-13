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

final class CashTransferAdmin extends TransactionAdmin
{
    protected $baseRoutePattern = 'cash_transfer';
    protected $baseRouteName = 'cash_transfer';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);

        $now = new DateTime();

        $cashAccountType = TransactionType::CASH_TRANSFER;

        $editRoute = $this->isCurrentRoute('edit');

        /* TODO: simplify the accountData session operation, put in separate service */

        if ($editRoute) {
            /** @var Transaction $subject */
            $subject = $this->getSubject();

            $mainAccount = $subject->getMainAccount();
            $accountId = $mainAccount->getId();
            $accountCurrency = $mainAccount->getCurrency();
            $unitId = $mainAccount->getUnit()->getId();

            $accountData = [];
            $accountData['accountId'] = $accountId;
            $accountData['accountCurrency'] = $accountCurrency;
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
            $accountCurrency = $accountData['accountCurrency'] ?? null;
            $unitId = $accountData['unitId'] ?? null;
            $mainAccount = $accountRepository->findOneBy(['id' => $accountId]);
        }

        $form
            ->with('accountName', [
                'label' => $mainAccount->getNameWithCurrencyBalance() . ' - Cash Transfer',
                'class' => 'col-md-5'
            ])
                ->add('date', DatePickerType::class, [
                    'years' => range(1900, $now->format('Y')),
                    'dp_max_date' => $now->format('c'),
                    'required' => true,
                    'format' => 'dd.MM.yyyy',
                ])
                ->add('transactionType', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => TransactionType::NAMES,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('tt')
                            ->where('tt.name = :cashTransfer')
                            ->setParameter('cashTransfer', 'Cash Transfer');
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
                //             ->where('tt.name = :cashTransfer')
                //             ->setParameter('cashTransfer', 'Cash transfer');
                //     },
                // ])
        ;

        if ($editRoute && $subject->getTransferFromAccount() == null) {
            $form
                ->add('transferToAccount', EntityType::class, [
                    'label' => 'To Account',
                    'class' => Account::class,
                    'disabled' => true
                ])
            ;
        } else {
            $form
                ->add('transferToAccount', EntityType::class, [
                    'label' => 'To Account',
                    'class' => Account::class,
                    'choice_label' => 'nameWithCurrencyBalance',
                    // 'query_builder' => function (EntityRepository $er)
                    //     use ($accountId, $cashAccountType, $accountCurrency, $unitId) {
                    //     return $er->createQueryBuilder('a')
                    //         ->where('a.id <> :account')
                    //         ->andWhere('a.accountType = :cashAccountType')
                    //         ->andWhere('a.currency = :currency')
                    //         ->andWhere('a.deactivated = false')
                    //         ->andWhere('a.unit = :unit')
                    //         ->setParameters([
                    //             'account' => $accountId,
                    //             'cashAccountType' => $cashAccountType,
                    //             'currency' => $accountCurrency,
                    //             'unit' => $unitId
                    //         ])
                    //         ->orderBy('a.name', 'ASC');
                    // },
                ])
            ;
        }
            $form
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'label' => 'Description',
                ])
                ->add('amount', MoneyType::class, [
                    'label' => 'Amount',
                    'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                    'currency' => $accountCurrency,
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
