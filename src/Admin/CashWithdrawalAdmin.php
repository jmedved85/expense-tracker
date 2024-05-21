<?php

declare(strict_types=1);

namespace App\Admin;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use App\Entity\Account;
use App\Entity\AccountType;
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

final class CashWithdrawalAdmin extends TransactionAdmin
{
    protected $baseRoutePattern = 'cash_withdrawal';
    protected $baseRouteName = 'cash_withdrawal';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->entityManager->getRepository(Account::class);

        $now = new DateTime();
        $subject = null;

        $cardAccountType = AccountType::CREDIT_CARD;
        // $cardAccountType = AccountType::DEBIT_CARD;

        $editRoute = $this->isCurrentRoute('edit');

        /* TODO: simplify the accountData session operation, put in separate service */

        if ($editRoute) {
            /** @var Transaction $subject */
            $subject = $this->getSubject();

            $unitId = $subject->getUnit()->getId();
            $accountFrom = $subject->getTransferFromAccount();
            $mainAccount = $subject->getMainAccount();
            $accountId = $accountFrom->getId();
            $accountFromCurrency = $accountFrom->getCurrency();
            $accountToCurrency = $mainAccount->getCurrency();

            $accountData = [];
            $accountData['accountId'] = $accountId;
            $accountData['accountFromCurrency'] = $accountFromCurrency;
            $accountData['accountToCurrency'] = $accountToCurrency;
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
                'label' => $mainAccount->getNameWithCurrencyBalance() . ' - Cash Withdrawal',
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
                            ->where('tt.name = :cashWithdrawal')
                            ->setParameter('cashWithdrawal', 'Cash Withdrawal');
                    },
                ])
                // ->add('transactionType', EntityType::class, [
                //     'class' => TransactionType::class,
                //     'label' => 'Transaction Type',
                //     'query_builder' => function (EntityRepository $er) {
                //         return $er->createQueryBuilder('tt')
                //             ->where('tt.name = :cash_withdrawal')
                //             ->setParameter('cash_withdrawal', 'Cash withdrawal');
                //     },
                // ])
                ->add('currency', TextType::class, [
                    'label' => 'Account Currency',
                    'data' => $editRoute ? $accountToCurrency : $accountCurrency,
                    'disabled' => true,
                ])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'label' => 'Description',
                ])
        ;

        if ($editRoute) {
            $form
                ->add('transferFromAccount', EntityType::class, [
                    'label' => 'From Account',
                    'class' => Account::class,
                    'choice_label' => 'nameWithCurrencyBalance',
                    'disabled' => true, // TODO: make not disabled -> preEdit updates needed
                ])
            ;
        } else {
            $form
                ->add('transferFromAccount', EntityType::class, [
                        'label' => 'From Account',
                        'class' => Account::class,
                        'placeholder' => 'Select an Account',
                        'choice_label' => 'nameWithCurrencyBalance',
                        // 'query_builder' => function (EntityRepository $er)
                        //     use ($accountId, $cardAccountTypeId, $unitId) {
                        //     return $er->createQueryBuilder('a')
                        //         ->where('a.id <> :account')
                        //         ->andWhere('a.accountType = :cardAccountType')
                        //         ->andWhere('a.deactivated = false')
                        //         ->andWhere('a.unit = :unit')
                        //         ->setParameters([
                        //             'account' => $accountId,
                        //             'cardAccountType' => $cardAccountTypeId,
                        //             'unit' => $unitId
                        //         ])
                        //         ->orderBy('a.name', 'ASC');
                        // },
                ])
            ;
        }

        $form
            ->add('amountFromAccount', MoneyType::class, [
                'label' => 'Amount from Account',
                // NOTE: Currency digit grouping; it is needed for validation to pass
                'grouping' => true,
                'required' => false,
                'currency' => $editRoute ? $accountFromCurrency : $accountCurrency,
                // 'help' => 'Amount from the account that is in another currency',
                'attr' => [
                    'style' => 'text-align:end;',
                ]
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                // NOTE: Currency digit grouping; it is needed for validation to pass
                'grouping' => true,
                'currency' => $editRoute ? $accountToCurrency : $accountCurrency,
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
