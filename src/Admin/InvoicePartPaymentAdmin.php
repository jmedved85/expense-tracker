<?php

declare(strict_types=1);

namespace App\Admin;

use DateTime;
use App\Entity\InvoicePartPayment;
use App\Traits\AdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class InvoicePartPaymentAdmin extends AbstractAdmin
{
    use AdminTrait;

    // MARK: - Datagrid Filters
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('datePaid')
            ->add('amount')
            ->add('currency')
        ;
    }

    // MARK: - List Fields
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('datePaid')
            ->add('amount')
            // ->add('currency')
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
        /** @var InvoicePartPayment $subject */
        $subject = $this->getSubject();

        $now = new DateTime();

        $datePaid = $subject->getDatePaid();
        $amount = $subject->getAmount();
        $realAmountPaid = $subject->getRealAmountPaid();
        $bankFeeAmount = $subject->getBankFeeAmount();
        $invoice = $subject->getInvoice();
        $account = null;
        $invoiceCurrency = null;
        $accountCurrency = null;

        if ($invoice) {
            $account = $invoice->getAccount();
            $invoiceCurrency = $invoice->getCurrency();
        }

        if ($account) {
            $accountCurrency = $account->getCurrency();
        }

        if ($subject->getMoneyReturnedAmount() == null && $subject->getMoneyReturnedDate() == null) {
            $form
                ->add('datePaid', DatePickerType::class, [
                    'years' => range(1900, $now->format('Y')),
                    'dp_max_date' => $now->format('c'),
                    'required' => false,
                    'format' => 'dd.MM.yyyy',
                    'attr' => [
                        // 'placeholder' => 'Pick a Date',
                        'style' =>
                            ($datePaid !== null)
                            ? 'background-color:#f2f2f2; pointer-events:none;'
                            : 'font-size:12px;',
                    ]
                ])
                ->add('amount', MoneyType::class, [
                    // 'currency' => $invoiceCurrency,
                    'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                    'currency' => false,
                    'required' => false,
                    'attr' => [
                        // 'placeholder' => 'Insert Value',
                        'style' =>
                            ($amount > 0)
                            ? 'text-align:end; background-color:#f2f2f2; pointer-events:none;'
                            : 'text-align:end; font-size:12px;',
                    ]
                ])
                ->add('realAmountPaid', MoneyType::class, [
                    'label' => 'Real Amount Paid',
                    'grouping' => true, // NOTE: Currency digit grouping; it is needed for validation to pass
                    // 'currency' => $accountCurrency,
                    'currency' => false,
                    'required' => false,
                    'attr' => [
                        // 'placeholder' => 'Insert Value',
                        'style' =>
                            (($realAmountPaid == null && $invoiceCurrency == $accountCurrency)
                                || $realAmountPaid > 0)
                            ? 'text-align:end; background-color:#f2f2f2; pointer-events:none;'
                            : 'text-align:end; font-size:12px;',
                    ]
                ])
                ->add('bankFeeAmount', TextType::class, [
                    'label' => 'Add Bank Fee',
                    // 'currency' => $accountCurrency,
                    'required' => false,
                    'attr' => [
                        'style' =>
                            ($bankFeeAmount > 0)
                            ? 'text-align:end; background-color:#f2f2f2; pointer-events:none;'
                            : 'text-align:end; font-size:12px;',
                        // 'placeholder' => 'Insert Value',
                    ],
                ])
                ->add('bankFeeNotApplicable', null, [
                    'label' => 'Bank Fee N/A',
                    'required' => false,
                    // 'attr' => [
                    //     'style' => 'width:20%;',
                    // ],
                ])
                ->add('restPayment', MoneyType::class, [
                    'label' => 'Due to Pay',
                    // NOTE: Currency digit grouping; it is needed for validation to pass
                    'grouping' => true,
                    // 'currency' => $invoiceCurrency,
                    'currency' => false,
                    'required' => false,
                    'disabled' => true,
                    'attr' => [
                        'style' => 'text-align:end;',
                    ]
                ])
                // ->add('file', CollectionType::class, [
                //     'label' => 'Upload File(s)',
                //     'required' => false,
                //     'help' => 'Supported file types: JPG/JPEG, PNG and PDF',
                //     'label_attr' => ['data-class' => 'lb-big'],
                //     'by_reference' => false,
                // ], [
                //     'edit' => 'inline',
                //     'inline' => 'form',
                //     'sortable'  => 'position',
                // ])
            ;
        }
    }

    // MARK: - Show Fields
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('datePaid')
            ->add('amount')
            // ->add('currency')
        ;
    }
}
