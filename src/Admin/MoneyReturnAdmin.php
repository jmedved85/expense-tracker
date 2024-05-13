<?php

declare(strict_types=1);

namespace App\Admin;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use App\Entity\Invoice;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\InvoiceRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class MoneyReturnAdmin extends TransactionAdmin
{
    protected $baseRoutePattern = 'money_return';
    protected $baseRouteName = 'money_return';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var InvoiceRepository $invoiceRepository */
        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->entityManager->getRepository(Transaction::class);

        $now = new DateTime();

        $editRoute = $this->isCurrentRoute('edit');

        /** @var Transaction $subject */
        $subject = $this->getSubject();

        $request = $this->getRequest();
        $moneyReturnedData = null;

        if (!$editRoute) {
            $moneyReturnedData = $request->query->all();

            if ($moneyReturnedData) {
                if (count($moneyReturnedData) == 1) {
                    $moneyReturnedData = $this->session()->get('moneyReturnedData');
                } else {
                    $this->session()->set('moneyReturnedData', $moneyReturnedData);
                }
            }
        }

        $invoiceId = $moneyReturnedData['invoiceId'] ?? $subject->getInvoice()->getId();
        $transactionId = $moneyReturnedData['transactionId'] ?? $subject->getId();

        $invoice = $invoiceRepository->findOneBy(['id' => $invoiceId]);
        $invoiceNumber = $invoice->getInvoiceNumber();

        $bankPaymentTransaction = null;

        if ($editRoute) {
            $invoiceTransactions = $invoice->getTransactions()->toArray();

            if (!empty($invoiceTransactions)) {
                foreach ($invoiceTransactions as $transaction) {
                    if ($transaction->getTransactionTypeName() == 'Bank Payment') {
                        $bankPaymentTransaction = $transaction;
                    }
                }
            }
        } else {
            $bankPaymentTransaction = $transactionRepository->findOneBy(['id' => $transactionId]);
        }

        $transactionNumber = $bankPaymentTransaction->getTransactionNumber();

        $currency = $bankPaymentTransaction->getCurrency();

        $account = $invoice->getAccount()
            ? $invoice->getAccount()->getNameWithCurrencyBalance()
            : $bankPaymentTransaction->getMainAccount()->getNameWithCurrencyBalance();

        $form
            ->with('transactionNumber', [
                'label' => 'Money Return on Invoice nr. ' . '"' . $invoiceNumber . '"',
                'class' => 'col-md-5 sepBefore'
            ])
                ->add('account', null, [
                    'label' => 'Account',
                    'data' => $account,
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'disabled' => true,
                        'readonly' => true,
                    ],
                ])
                ->add('invoiceNumber', null, [
                    'label' => 'Invoice Number',
                    'data' => $invoice->getInvoiceNumber(),
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'disabled' => true,
                        'readonly' => true,
                    ],
                ])
                ->add('transactionNumber', NumberType::class, [
                    'label' => 'Bank Payment Transaction No',
                    'data' => $transactionNumber,
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'disabled' => true,
                        'readonly' => true,
                    ],
                ])
                ->add('transactionType', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => TransactionType::NAMES,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('tt')
                            ->where('tt.name = :moneyReturned')
                            ->setParameter('moneyReturned', 'Money Returned');
                    },
                    'attr' => [
                        'style' => 'display:none;',
                    ],
                ])
                // ->add('transactionType', EntityType::class, [
                //     'class' => TransactionType::class,
                //     'label' => 'Transaction Type',
                //     'query_builder' => function (EntityRepository $er) {
                //         return $er->createQueryBuilder('tt')
                //             ->where('tt.name = :moneyReturned')
                //             ->setParameter('moneyReturned', 'Money returned');
                //     },
                //     'attr' => [
                //         'style' => 'display:none;',
                //     ],
                // ])
        ;

        if (!$editRoute) {
            $form
                ->add('transaction', EntityType::class, [
                    'class' => Transaction::class,
                    'label' => 'Transaction',
                    'data' => $bankPaymentTransaction,
                ])
                ->add('invoice', EntityType::class, [
                    'class' => Invoice::class,
                    'label' => 'Invoice',
                    'data' => $invoice,
                ])
            ;
        }

        $form
            ->add('date', DatePickerType::class, [
                'label' => 'Date of Money Returned',
                'years' => range(1900, $now->format('Y')),
                'dp_max_date' => $now->format('c'),
                'required' => false,
                'format' => 'dd.MM.yyyy',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Money Returned Amount',
                // NOTE: Currency digit grouping; it is needed for validation to pass
                'grouping' => true,
                'currency' => $currency,
                'attr' => [
                    'style' => 'text-align:end;',
                ],
                'required' => true,
            ])
            ->end()
            ->with('File Uploads', ['class' => 'col-md-5 mt-5'])
                // ->add('file', CollectionType::class, [
                //     'label' => false,
                //     'required' => false,
                //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF',
                //     'label_attr' => ['data-class' => 'lb-big'],
                //     'by_reference' => false,
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
