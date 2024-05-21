document.addEventListener("DOMContentLoaded", function() {
    /* INVOICE CREATE FORM - currency symbol on Amount total invoice field */
    const invoiceCreateForm = document.querySelector('form[action*="/admin/app/invoice/"][action*="/create"]');
    const invoiceAddNewCreateForm = document.querySelector('form[action^="/admin/app/invoice/create"]');
    const invoiceEditForm = document.querySelector('form[action^="/admin/app/invoice"][action*="/edit"]');
    
    if (invoiceAddNewCreateForm || invoiceCreateForm || invoiceEditForm) {
        const invoiceAccountSelectFieldInvoice = document.querySelector('select[id$="_account"]') as HTMLSelectElement;
        const currencySelectFieldInvoice = document.querySelector('select[id$="_currency"]') as HTMLSelectElement;
        const currencyTextFieldInvoice = document.querySelector('input[id$="_currency"]') as HTMLInputElement;
        const amountTotalInvoiceContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]') as HTMLDivElement;
        const realAmountPaidInvoiceContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_realAmountPaid"]') as HTMLDivElement;
        const bankFeeInvoiceContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_bankFeeAmount"]') as HTMLDivElement;
        const invoiceLinesContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoiceLines"]') as HTMLDivElement;
        const budgetMainCategorySelect = document.querySelector('select[id$="_budgetMainCategory"]') as HTMLSelectElement;
        const invoiceDateInput = document.querySelectorAll('.input-group.date') as NodeListOf<HTMLElement>;
        // const addNewButtonInvoiceLines = invoiceLinesContainer.querySelector('a[title="Add new"]');

        invoiceDateInput.forEach(item => {
            item.style.width = '35%';
        });

        let accountCurrencyCode: string | undefined;
        let selectedCurrencyCode: string;
        let mainCategoryId: string;

        if (invoiceAccountSelectFieldInvoice && currencySelectFieldInvoice && budgetMainCategorySelect) {
            if (invoiceAccountSelectFieldInvoice as HTMLSelectElement && (currencySelectFieldInvoice as HTMLSelectElement).value === '') {
                if (amountTotalInvoiceContainer) {
                    const currencySymbolElementAmountTotal = amountTotalInvoiceContainer.querySelector('.input-group-addon');

                    if (currencySymbolElementAmountTotal) {
                        currencySymbolElementAmountTotal.textContent = '\u00A0\u00A0';
                    }
                }
        
                if (realAmountPaidInvoiceContainer) {
                    const currencySymbolElementRealAmountPaid = realAmountPaidInvoiceContainer.querySelector('.input-group-addon');

                    if (currencySymbolElementRealAmountPaid) {
                        currencySymbolElementRealAmountPaid.textContent = '\u00A0\u00A0';
                    }
                }
    
                if (bankFeeInvoiceContainer) {
                    const currencySymbolElementBankFeeAmount = bankFeeInvoiceContainer.querySelector('.input-group-addon');

                    if (currencySymbolElementBankFeeAmount) {
                        currencySymbolElementBankFeeAmount.textContent = '\u00A0\u00A0';
                    }
                }
            }
        }

        if (invoiceAccountSelectFieldInvoice) {
            $(invoiceAccountSelectFieldInvoice).on('select2:select', (e) => {
                const currencySymbolElementRealAmountPaid = realAmountPaidInvoiceContainer.querySelector('.input-group-addon') as HTMLElement;
                const currencySymbolElementBankFeeAmount = bankFeeInvoiceContainer.querySelector('.input-group-addon') as HTMLElement;
                const invoiceRealAmountPaidContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_realAmountPaid"]') as HTMLDivElement;
                const realAmountPaidInvoiceInput = realAmountPaidInvoiceContainer.querySelector('input[id$="_realAmountPaid"]') as HTMLInputElement;
                const invoicePaymentStatusSelect = document.querySelector('select[id$="_invoicePaymentStatus"]') as HTMLSelectElement;
                const regex = /\((.*?)\)/;
                let selectedOption = e.params.data as { text: string };
                const matches = selectedOption.text.match(regex);
    
                /* TODO: fix Add New Invoice Paying bank Account select "Real Amount Paid" currency symbol */
    
                if (matches && matches.length > 1) {
                    accountCurrencyCode = matches[1];
                    const currencyPart = new Intl.NumberFormat('en', { style: 'currency', currency: accountCurrencyCode })
                        .formatToParts(0)
                        .find(part => part.type === 'currency');
                    const currencySymbol = currencyPart ? currencyPart.value.trim() : '';
    
                    if (currencySymbolElementRealAmountPaid) {
                        currencySymbolElementRealAmountPaid.textContent = currencySymbol;
                    }
    
                    if (currencySymbolElementBankFeeAmount) {
                        currencySymbolElementBankFeeAmount.textContent = currencySymbol;
                    }
                }
    
                if (selectedCurrencyCode === undefined) {
                    if (currencySelectFieldInvoice) {
                        selectedCurrencyCode = currencySelectFieldInvoice.value;
                    } else if (currencyTextFieldInvoice) {
                        selectedCurrencyCode = currencyTextFieldInvoice.value;
                    } else {
                        selectedCurrencyCode = '';
                    }
                }
    
                if (accountCurrencyCode !== undefined && selectedCurrencyCode !== undefined) {
                    if (accountCurrencyCode === selectedCurrencyCode) {
                        if (invoiceRealAmountPaidContainer) {
                            invoiceRealAmountPaidContainer.style.display = 'none';
                            realAmountPaidInvoiceInput.required = false;
                        }
                    } else {
                        realAmountPaidInvoiceContainer.style.display = 'block';
                        realAmountPaidInvoiceInput.required = true;
                    }
                }
    
                if (invoicePaymentStatusSelect.value == 'Part-Paid') {
                    // invoicePartPaymentsContainer.style.display = 'block';
                    // invoiceTotalPaidContainer.style.display = 'block';
                    invoiceRealAmountPaidContainer.style.display = 'none';
                    realAmountPaidInvoiceInput.required = false;
                }
            })
        }

        if (currencySelectFieldInvoice) {
            $(currencySelectFieldInvoice).on('select2:select', (e) => {
                if (realAmountPaidInvoiceContainer && amountTotalInvoiceContainer) {
                    const invoicePaymentStatusSelect = document.querySelector('select[id$="_invoicePaymentStatus"]') as HTMLSelectElement;
                    const realAmountPaidInvoiceInput = realAmountPaidInvoiceContainer.querySelector('input[id$="_realAmountPaid"]') as HTMLInputElement;
                    const currencySymbolElementAmountTotal = amountTotalInvoiceContainer.querySelector('.input-group-addon') as HTMLElement;
                    const selectedOption = (currencySelectFieldInvoice as HTMLSelectElement).options[(currencySelectFieldInvoice as HTMLSelectElement).selectedIndex] as HTMLOptionElement;
                    let selectedCurrencyCode = selectedOption.value;
    
                    const currencyPart = new Intl.NumberFormat('en', { style: 'currency', currency: selectedCurrencyCode })
                        .formatToParts(0)
                        .find(part => part.type === 'currency');
                    const currencySymbol = currencyPart ? currencyPart.value.trim() : '';
    
                    if (currencySymbolElementAmountTotal) {
                        currencySymbolElementAmountTotal.textContent = currencySymbol;
                    }
    
                    if (accountCurrencyCode === undefined) {
                        accountCurrencyCode = getAccountCurrencyCode(invoiceAccountSelectFieldInvoice);
                    }
        
                    if (accountCurrencyCode !== undefined && selectedCurrencyCode !== undefined) {
                        if (accountCurrencyCode === selectedCurrencyCode) {
                            realAmountPaidInvoiceContainer.style.display = 'none';
                            realAmountPaidInvoiceInput.required = false;
                        } else {
                            if (invoicePaymentStatusSelect.value !== 'Unpaid') {
                                realAmountPaidInvoiceContainer.style.display = 'block';
                                realAmountPaidInvoiceInput.required = true;
                            }
                        }
                    }
                }
            })
        }

        /* Amount (Invoice Lines Total) calculation && Budget Category filtration */
        let invoiceAmountTotal = amountTotalInvoiceContainer.querySelector('input[id$="_amount"]') as HTMLInputElement;
        let selectedBudgetCategories: any[] = [];

        invoiceLinesContainer.addEventListener('click', (e) => {
            if ((e.target as Element).matches('a[title="Add new"]')) {
                /* INVOICE TOTAL SUM CALCULATION */
                let lineTotalsSum = 0;

                const lineTotals = invoiceLinesContainer.querySelectorAll('input[id$="_lineTotal"]');

                lineTotals.forEach((lineTotal) => {
                    let lineTotalValue = (lineTotal as HTMLInputElement).value.replace(/,/g, '');
                    let lineTotalValueFloat = parseFloat(lineTotalValue);

                    if (!isNaN(lineTotalValueFloat)) {
                        lineTotalsSum += lineTotalValueFloat;
                    }
                });

                const formattedSum: string = lineTotalsSum.toFixed(2);

                (invoiceAmountTotal as HTMLInputElement).value = formattedSum;
                
                const invoiceLineItems: NodeListOf<Element> = invoiceLinesContainer.querySelectorAll('.ui-sortable-handle');

                invoiceLineItems.forEach((item) => {
                    const budgetMainCategorySelect: HTMLSelectElement | null = item.querySelector('select[id*="_budgetMainCategory"]') as HTMLSelectElement;
                    const budgetCategorySelect: HTMLSelectElement | null = item.querySelector('select[id*="_budgetCategory"]') as HTMLSelectElement;

                    if (budgetMainCategorySelect.selectedIndex > 0) {
                        const categoryElementId = budgetCategorySelect.id;
                        const categoryOptions = budgetCategorySelect.querySelectorAll('option');
                        const optionsArr: { id: string; name: string; selected: boolean; }[] = [];

                        categoryOptions.forEach((option: HTMLOptionElement) => {
                            const optionIdText = {
                                id: option.value,
                                name: option.text,
                                selected: option.selected == true ? true : false
                            };

                            optionsArr.push(optionIdText);
                        });
                        
                        const categoryObj = {
                            key: categoryElementId,
                            value: optionsArr
                        };

                        selectedBudgetCategories.push(categoryObj);
                    }
                });

                /* FILTERING BUDGET CATEGORIES IF MAIN CATEGORY IS SELECTED */
                function checkForInvoiceLineRowToOpen() {
                    const invoiceLinesRows: Element | null = invoiceLinesContainer.querySelector('.sonata-ba-tbody.ui-sortable')
                        ?? invoiceLinesContainer.querySelector('.sonata-ba-collapsed-fields');

                    if (invoiceLinesRows) {
                        const invoiceLineTableStyleItems: NodeListOf<Element> = invoiceLinesContainer.querySelectorAll('.ui-sortable-handle');
                        const invoiceLineFormStyleItems: NodeListOf<Element> = invoiceLinesContainer.querySelectorAll('fieldset');
                    
                        const invoiceLineItems: NodeListOf<Element> 
                            = invoiceLineTableStyleItems.length > 0 ? invoiceLineTableStyleItems : invoiceLineFormStyleItems;

                        invoiceLineItems.forEach((item: Element) => {
                            let itemParent: Element | null = item.parentElement;

                            if (!(itemParent && itemParent.id.endsWith('file'))) {
                                const budgetMainCategorySelect: HTMLSelectElement | null 
                                    = item.querySelector('select[id*="_budgetMainCategory"]') as HTMLSelectElement;
                                const budgetCategorySelect: HTMLSelectElement | null 
                                    = item.querySelector('select[id*="_budgetCategory"]') as HTMLSelectElement;
                                const netValueInput: HTMLInputElement | null 
                                    = item.querySelector('input[id*="_netValue"]');
                                const vatInput: HTMLInputElement | null 
                                    = item.querySelector('input[id*="_vat"]');
                                const vatValueInput: HTMLInputElement | null 
                                    = item.querySelector('input[id*="_vatValue"]');
                                const lineTotalInput: HTMLInputElement | null 
                                    = item.querySelector('input[id*="_lineTotal"]');

                                if (Array.isArray(selectedBudgetCategories)) {
                                    if (selectedBudgetCategories.length > 0) {
                                        selectedBudgetCategories.forEach((selectedCategory) => {
                                            if (budgetMainCategorySelect && budgetMainCategorySelect.selectedIndex > 0) {
                                                if (budgetCategorySelect.id == selectedCategory.key) {
                                                    updateBudgetCategoryListInvoiceCreateEdit(budgetCategorySelect, selectedCategory.value);
                                                }
                                            }
                                        });
                                    }
                                }

                                if (budgetMainCategorySelect.selectedIndex === 0) {
                                    budgetCategorySelect.innerHTML = '';
                                }

                                $(budgetMainCategorySelect).on('select2:select', (e) => {
                                    const selectedOption = budgetMainCategorySelect.options[budgetMainCategorySelect.selectedIndex];
                                    mainCategoryId = selectedOption.value;
                        
                                    fetch('/get_budget_categories', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({ mainCategoryId: mainCategoryId }), 
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        // console.log(data); // Handle response from the server
                                        updateBudgetCategoryListInvoiceCreateEdit(budgetCategorySelect, data);
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                    });
                                });

                                if (netValueInput) {
                                    $(netValueInput).on({
                                        keyup: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                        },
                                        blur: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                        }
                                    });
                                }

                                if (vatInput) {
                                    $(vatInput).on({
                                        keyup: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                        },
                                        blur: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                        }
                                    });
                                }

                                if (vatValueInput) {
                                    $(vatValueInput).on({
                                        keyup: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                        },
                                        blur: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                        }
                                    });
                                }

                                if (lineTotalInput) {
                                    $(lineTotalInput).on({
                                        keyup: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                        },
                                        blur: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                        }
                                    });
                                }
                            }
                        });

                        clearInterval(interval);
                    }
                }

                const interval = setInterval(checkForInvoiceLineRowToOpen, 1200);
            }
        });

        if (invoiceEditForm) {
            const invoiceLinesRows: Element | null = invoiceLinesContainer.querySelector('.sonata-ba-tbody.ui-sortable')
                ?? invoiceLinesContainer.querySelector('.sonata-ba-collapsed-fields');

            if (invoiceLinesRows) {
                const invoiceLineTableStyleItems: NodeListOf<Element> = invoiceLinesContainer.querySelectorAll('.ui-sortable-handle');
                const invoiceLineFormStyleItems: NodeListOf<Element> = invoiceLinesContainer.querySelectorAll('fieldset');

                const invoiceLineItems: NodeListOf<Element> 
                    = invoiceLineTableStyleItems.length > 0 ? invoiceLineTableStyleItems : invoiceLineFormStyleItems;

                invoiceLineItems.forEach((item) => {
                    let itemParent: Element | null = item.parentElement;

                    if (!(itemParent && itemParent.id.endsWith('file'))) {
                        const budgetMainCategorySelect: HTMLSelectElement | null 
                            = item.querySelector('select[id*="_budgetMainCategory"]') as HTMLSelectElement;
                        const budgetCategorySelect: HTMLSelectElement | null 
                            = item.querySelector('select[id*="_budgetCategory"]') as HTMLSelectElement;
                        const netValueInput: HTMLInputElement | null = item.querySelector('input[id*="_netValue"]');
                        const vatInput: HTMLInputElement | null = item.querySelector('input[id*="_vat"]');
                        const vatValueInput: HTMLInputElement | null = item.querySelector('input[id*="_vatValue"]');
                        const lineTotalInput: HTMLInputElement | null = item.querySelector('input[id*="_lineTotal"]');

                        if (budgetMainCategorySelect.selectedIndex === 0) {
                            budgetCategorySelect.innerHTML = '';
                        }

                        $(budgetMainCategorySelect).on('select2:select', (e) => {
                            const selectedOption = budgetMainCategorySelect.options[budgetMainCategorySelect.selectedIndex];
                            mainCategoryId = selectedOption.value;

                            fetch('/get_budget_categories', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ mainCategoryId: mainCategoryId }), 
                            })
                            .then(response => response.json())
                            .then(data => {
                                // console.log(data); // Handle response from the server
                                updateBudgetCategoryListInvoiceCreateEdit(budgetCategorySelect, data);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                        });

                        if (netValueInput) {
                            $(netValueInput).on({
                                keyup: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                },
                                blur: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                }
                            });
                        }

                        if (vatInput) {
                            $(vatInput).on({
                                keyup: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                },
                                blur: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                }
                            });
                        }

                        if (vatValueInput) {
                            $(vatValueInput).on({
                                keyup: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                },
                                blur: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                }
                            });
                        }

                        if (lineTotalInput) {
                            $(lineTotalInput).on({
                                keyup: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                },
                                blur: function() {
                                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                                }
                            });
                        }
                    }
                });
            }
        }

        function updateBudgetCategoryListInvoiceCreateEdit(
            budgetCategorySelect: HTMLSelectElement, 
            filteredBudgetCategories: { id: string; name: string; selected: boolean; }[]
        ) {
            budgetCategorySelect.innerHTML = '';

            if (filteredBudgetCategories) {
                const newBudgetCategorySelect: HTMLSelectElement = document.createElement('select');
                newBudgetCategorySelect.id = budgetCategorySelect.id;
                newBudgetCategorySelect.name = budgetCategorySelect.name;
                newBudgetCategorySelect.className = budgetCategorySelect.className;
                newBudgetCategorySelect.setAttribute('data-placeholder', 'Choose a Sub-Category');
    
                for (const category of filteredBudgetCategories) {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.text = category.name;
                    option.selected = category.selected;

                    budgetCategorySelect.appendChild(option);
                }
    
                $(budgetCategorySelect).trigger('change');
            }
        }

        /* EXAMPLE OF THE FUNCTION THAT RETURNS PROMISE */
        // function fetchBudgetCategoryList(mainCategoryId, budgetCategorySelect = null) {
        //     return new Promise((resolve, reject) => {
        //         fetch('/get_budget_categories', {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/json',
        //             },
        //             body: JSON.stringify({ mainCategoryId: mainCategoryId }), 
        //         })
        //         .then(response => response.json())
        //         .then(data => {
        //             console.log(data); // Handle response from the server
        //             const categoryList = data;
        
        //             if (budgetCategorySelect) {
        //                 updateBudgetCategoryList(budgetCategorySelect, categoryList);
        //             }
        
        //             resolve(categoryList);
        //         })
        //         .catch(error => {
        //             console.error('Error:', error);
        //             reject(error);
        //         });
        //     });
        // }
    }

    // const invoiceCreateForm: HTMLFormElement | null = document.querySelector('form[action*="/admin/app/invoice/"]');
    // const invoiceCurrencyContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_currency"]');
    const invoiceAccountContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_account"]');
    const invoicePaymentStatusSelect: HTMLSelectElement | null = document.querySelector('select[id$="_invoicePaymentStatus"]');
    const invoicePartPaymentsContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoicePartPayments"]');
    const invoiceDatePaidContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoiceDatePaid"]');
    const invoiceRealAmountPaidContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_realAmountPaid"]');
    const invoiceBankFeeAmountContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_bankFeeAmount"]');
    const bankFeeAmountInputHelp: HTMLParagraphElement | null = document.querySelector('p[id$="_bankFeeAmount_help"]');
    const invoicePartPaymentsElementFieldWidget: HTMLSpanElement | null = document.querySelector('span[id^="field_widget_"][id$="_invoicePartPayments"]');
    const invoiceAmountContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
    const invoiceTotalPaidContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_totalPaid"]');
    const partPaymentDatePaidInput: NodeListOf<HTMLInputElement> = document.querySelectorAll('input[id*="_invoicePartPayments_"][id*="_datePaid"]');
    const partPaymentAmountInput: NodeListOf<HTMLInputElement> = document.querySelectorAll('input[id*="_invoicePartPayments_"][id*="_amount"]');
    const partPaymentBankFeeInput: NodeListOf<HTMLInputElement> = document.querySelectorAll('input[id*="_invoicePartPayments_"][id*="_bankFeeAmount"]');
    const invoiceAccountSelectFieldInvoice: HTMLSelectElement | null = document.querySelector('select[id$="_account"]');
    const currencySelectFieldInvoice: HTMLSelectElement | null = document.querySelector('select[id$="_currency"]');
    const currencyTextFieldInvoice: HTMLInputElement | null = document.querySelector('input[id$="_currency"]');
    let invoiceMoneyReturnedContainer: HTMLDivElement | null;

    let colMd6Elements: HTMLCollectionOf<Element> = document.getElementsByClassName("col-md-6");

    if (colMd6Elements) {
        for (let i = 0; i < colMd6Elements.length; i++) {
            let h4Element = colMd6Elements[i].querySelector(".box-title") as HTMLDivElement;
            if (h4Element && h4Element.textContent && h4Element.textContent.trim() === "Money Returns") {
                invoiceMoneyReturnedContainer = colMd6Elements[i] as HTMLDivElement;
            }
        }
    }

    let accountCurrencyCode: string | undefined = "";
    let selectedCurrencyCode: string = "";

    if (invoiceAccountSelectFieldInvoice) {
        accountCurrencyCode = getAccountCurrencyCode(invoiceAccountSelectFieldInvoice);
    }

    if (currencySelectFieldInvoice) {
        selectedCurrencyCode = currencySelectFieldInvoice.value;
    } else if (currencyTextFieldInvoice) {
        selectedCurrencyCode = currencyTextFieldInvoice.value;
    } else {
        selectedCurrencyCode = '';
    }

    let bankFeeAmountInput: HTMLInputElement | null = null;
    let bankFeeAmountInputLabel: HTMLLabelElement | null = null;

    if (invoiceBankFeeAmountContainer) {
        bankFeeAmountInput = invoiceBankFeeAmountContainer.querySelector('input[type="text"]');
        bankFeeAmountInputLabel = invoiceBankFeeAmountContainer.querySelector('label[for$="_bankFeeAmount"]');

        /* Amount formatting */
        if (bankFeeAmountInput) {
            $(bankFeeAmountInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        };
    }

    if (invoiceRealAmountPaidContainer) {
        const realAmountPaidInput: HTMLInputElement | null 
            = invoiceRealAmountPaidContainer ? invoiceRealAmountPaidContainer.querySelector('input[type="text"]') : null;

        /* Amount formatting */
        if (realAmountPaidInput) {
            $(realAmountPaidInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }
    }

    if (invoicePaymentStatusSelect) {
        const invoiceAccountSelect: HTMLSelectElement | null 
            = invoiceAccountContainer ? invoiceAccountContainer.querySelector('select[id$="_account"]') : null;
        const realAmountPaidInvoiceInput: HTMLInputElement | null 
            = invoiceRealAmountPaidContainer ? invoiceRealAmountPaidContainer.querySelector('input[id$="_realAmountPaid"]') : null;

        /* NOTE: Temporary solution */
        if (invoicePaymentStatusSelect.value === 'Paid' && invoicePartPaymentsContainer) {
            const partPaidSelectOption: HTMLOptionElement | null 
                = invoicePaymentStatusSelect ? invoicePaymentStatusSelect.querySelector('option[value="Part-Paid"]') : null;

            if (invoiceRealAmountPaidContainer) {
                invoiceRealAmountPaidContainer.style.display = 'none';
            }
            
            if (partPaidSelectOption) {
                partPaidSelectOption.remove();
            }
            
            const partPaymentAddNewButton 
                = invoicePartPaymentsContainer ? invoicePartPaymentsContainer.querySelector('.sonata-ba-action') : null;
            if (partPaymentAddNewButton) {
                (partPaymentAddNewButton as HTMLElement).style.display = 'none';
            }
            
            if (partPaymentDatePaidInput && partPaymentAmountInput && partPaymentBankFeeInput) {
                partPaymentDatePaidInput.forEach(item => {
                    if (item.value) {
                        item.readOnly = true;
                    }
                });
            
                partPaymentAmountInput.forEach(item => {
                    if (item.value) {
                        item.readOnly = true;
                    }
                });
            }
        } else if (invoicePaymentStatusSelect.value === 'Part-Paid' && invoicePartPaymentsContainer) {
            const paidSelectOption: HTMLOptionElement | null 
                = invoicePaymentStatusSelect ? invoicePaymentStatusSelect.querySelector('option[value="Paid"]') : null;
            if (paidSelectOption) {
                paidSelectOption.remove();
            }
        }

        if (invoicePaymentStatusSelect.value == 'Unpaid') {
            if (invoiceAccountContainer) {
                invoiceAccountContainer.style.display = 'none';
            }
            if (invoiceAccountSelect) {
                invoiceAccountSelect.required = false;
            }
            if (invoicePartPaymentsContainer) {
                invoicePartPaymentsContainer.style.display = 'none';
            }
            if (invoiceTotalPaidContainer) {
                invoiceTotalPaidContainer.style.display = 'none';
            }
            if (invoiceDatePaidContainer) {
                invoiceDatePaidContainer.style.display = 'none';
            }
            if (invoiceRealAmountPaidContainer) {
                invoiceRealAmountPaidContainer.style.display = 'none';
            }
            if (invoiceBankFeeAmountContainer) {
                invoiceBankFeeAmountContainer.style.display = 'none';
            }
        } else if (invoicePaymentStatusSelect.value == 'Paid') {
            if (invoicePartPaymentsContainer) {
                invoicePartPaymentsContainer.style.display = 'none';
            }
            if (invoiceAccountContainer) {
                invoiceAccountContainer.style.display = 'block';
            }
            if (invoiceAccountSelect) {
                invoiceAccountSelect.required = true;
            }

            if (invoicePartPaymentsElementFieldWidget) {
                if (invoicePartPaymentsElementFieldWidget.textContent) {
                    if (invoicePartPaymentsElementFieldWidget.textContent.trim() !== "") {
                        // let partPartPaymentsHasData: boolean = true;

                        if (invoicePartPaymentsContainer) {
                            invoicePartPaymentsContainer.style.display = 'block';
                        }

                        if (bankFeeAmountInput && bankFeeAmountInputLabel) {
                            bankFeeAmountInput.disabled = true;
                            bankFeeAmountInputLabel.textContent = 'Total Bank Fee';
                            // bankFeeAmountInputHelp.textContent = 'Total Bank Fee paid for the Part-Payment transactions';
                        }
                    }
                }
            }

            if (invoiceTotalPaidContainer) {
                invoiceTotalPaidContainer.style.display = 'none';
            }

            if (invoiceDatePaidContainer) {
                invoiceDatePaidContainer.style.display = 'block';
            }

            if (accountCurrencyCode == selectedCurrencyCode) {
                if (invoiceRealAmountPaidContainer) {
                    invoiceRealAmountPaidContainer.style.display = 'none';
                }
                
                if (realAmountPaidInvoiceInput) {
                    realAmountPaidInvoiceInput.required = false;
                }
            } else {
                if (invoiceRealAmountPaidContainer) {
                    invoiceRealAmountPaidContainer.style.display = 'block';
                }
                
                if (realAmountPaidInvoiceInput) {
                    realAmountPaidInvoiceInput.required = true;
                }
            }
        } else {
            if (invoiceAccountContainer) {
                invoiceAccountContainer.style.display = 'block';
            }
            
            if (invoiceAccountSelect) {
                invoiceAccountSelect.required = true;
            }
            
            if (invoicePartPaymentsContainer) {
                invoicePartPaymentsContainer.style.display = 'block';
            }
            
            if (invoiceTotalPaidContainer) {
                invoiceTotalPaidContainer.style.display = 'block';
            }
            
            if (invoiceDatePaidContainer) {
                invoiceDatePaidContainer.style.display = 'none';
            }

            if (accountCurrencyCode == selectedCurrencyCode) {
                if (invoiceRealAmountPaidContainer) {
                    invoiceRealAmountPaidContainer.style.display = 'none';
                }
            }

            if (invoicePartPaymentsElementFieldWidget) {
                if (invoicePartPaymentsElementFieldWidget.textContent) {
                    if (invoicePartPaymentsElementFieldWidget.textContent.trim() !== "") {
                        if (invoiceBankFeeAmountContainer) {
                            invoiceBankFeeAmountContainer.style.display = 'block';
                        }

                        if (bankFeeAmountInput && bankFeeAmountInputLabel) {
                            bankFeeAmountInput.disabled = true;
                            bankFeeAmountInputLabel.textContent = 'Total Bank Fee';
                            // bankFeeAmountInputHelp.textContent = 'Total Bank Fee paid for the Part-Payment transactions';
                        }
                    }
                }
            } else {
                if (invoiceBankFeeAmountContainer) {
                    invoiceBankFeeAmountContainer.style.display = 'none';
                }
            }
        }
    }

    if (invoicePartPaymentsContainer) {
        if (invoicePaymentStatusSelect) {
            let currentSelection: string = invoicePaymentStatusSelect.value;
            const invoiceAccountSelect: HTMLSelectElement | null 
                = invoiceAccountContainer ? invoiceAccountContainer.querySelector('select[id$="_account"]') : null;
            // const invoiceDatePaidInput: HTMLInputElement | null 
            //     = invoiceDatePaidContainer ? invoiceDatePaidContainer.querySelector('input[id$="_invoiceDatePaid"]') : null;
            // const invoiceRealAmountPaidInput: HTMLInputElement | null 
            //     = invoiceRealAmountPaidContainer ? invoiceRealAmountPaidContainer.querySelector('input[id$="_realAmountPaid"]') : null;

            let invoiceAmountValue: number | undefined;
            let invoiceTotalPaidValue: number | undefined;

            if (invoiceAmountContainer) {
                const inputElement: HTMLInputElement | null = invoiceAmountContainer.querySelector('input[id$="_amount"]');
                invoiceAmountValue = inputElement ? parseFloat(inputElement.value) : undefined;
            }

            if (invoiceTotalPaidContainer) {
                const inputElement: HTMLInputElement | null = invoiceTotalPaidContainer.querySelector('input[id$="_totalPaid"]');
                invoiceTotalPaidValue = inputElement ? parseFloat(inputElement.value) : undefined;
            }

            $(invoicePaymentStatusSelect).on('select2:select', (e) => {
                let selectedOption = e.params.data;
    
                switch (selectedOption.id) {
                    case 'Unpaid':
                        if (invoiceAccountContainer) {
                            invoiceAccountContainer.style.display = 'none';
                        }

                        if (invoiceAccountSelect) {
                            invoiceAccountSelect.required = false;
                        }

                        if (invoicePartPaymentsContainer) {
                            invoicePartPaymentsContainer.style.display = 'none';
                        }

                        if (invoiceDatePaidContainer) {
                            invoiceDatePaidContainer.style.display = 'none';
                        }

                        if (invoiceRealAmountPaidContainer) {
                            invoiceRealAmountPaidContainer.style.display = 'none';
                        }

                        if (invoiceBankFeeAmountContainer) {
                            invoiceBankFeeAmountContainer.style.display = 'none';
                        }

                        if (invoiceTotalPaidContainer) {
                            invoiceTotalPaidContainer.style.display = 'none';
                        }

                        if (invoiceMoneyReturnedContainer) {
                            invoiceMoneyReturnedContainer.style.display = 'none';
                        }

                        break;
                    case 'Paid':
                        if (invoiceAccountContainer) {
                            invoiceAccountContainer.style.display = 'block';
                        }

                        if (invoiceAccountSelect) {
                            invoiceAccountSelect.required = true;
                        }

                        if (invoiceDatePaidContainer) {
                            invoiceDatePaidContainer.style.display = 'block';
                        }

                        // if (invoiceRealAmountPaidContainer) {
                        //     invoiceRealAmountPaidContainer.style.display = 'block';
                        // }

                        if (invoicePartPaymentsContainer) {
                            invoicePartPaymentsContainer.style.display = 'none';
                        }

                        if (invoiceBankFeeAmountContainer) {
                            invoiceBankFeeAmountContainer.style.display = 'block';
                        }

                        if (currentSelection == 'Part-Paid') {
                            if (invoiceTotalPaidContainer) {
                                invoiceTotalPaidContainer.style.display = 'none';
                            }

                            if (bankFeeAmountInput) {
                                bankFeeAmountInput.disabled = false;
                                // bankFeeAmountInput.value = '0.00';
                            }

                            if (bankFeeAmountInputLabel) {
                                bankFeeAmountInputLabel.textContent = 'Bank Fee';
                            }
                            
                            // if (bankFeeAmountInputHelp) {
                            //     bankFeeAmountInputHelp.textContent = "Insert value in 'X,XXX.XX' format";
                            // }
                        }

                        if (invoicePartPaymentsElementFieldWidget) {
                            if (currentSelection !== 'Part-Paid') {
                                if (invoicePartPaymentsElementFieldWidget.textContent) {
                                    if (invoicePartPaymentsElementFieldWidget.textContent.trim() !== "") {
                                        if (invoiceTotalPaidContainer) {
                                            invoiceTotalPaidContainer.style.display = 'block';
                                        }
                                    } else {
                                        if (invoiceTotalPaidContainer) {
                                            invoiceTotalPaidContainer.style.display = 'none';
                                        }
                                    }
                                }
                            }
                        }

                        if (invoiceAmountValue == invoiceTotalPaidValue) {
                            if (invoiceMoneyReturnedContainer) {
                                invoiceMoneyReturnedContainer.style.display = 'block';
                            }
                        }
                        
                        break;
                    case 'Part-Paid':
                        if (invoiceAccountContainer) {
                            invoiceAccountContainer.style.display = 'block';
                        }
                        
                        if (invoiceAccountSelect) {
                            invoiceAccountSelect.required = true;
                        }
                        
                        if (invoicePartPaymentsContainer) {
                            invoicePartPaymentsContainer.style.display = 'block';
                        }
                        
                        if (invoiceTotalPaidContainer) {
                            invoiceTotalPaidContainer.style.display = 'block';
                        }
                        
                        if (invoiceDatePaidContainer) {
                            invoiceDatePaidContainer.style.display = 'none';
                        }
                        
                        if (invoiceRealAmountPaidContainer) {
                            invoiceRealAmountPaidContainer.style.display = 'none';
                        }
                        
                        if (invoiceBankFeeAmountContainer) {
                            invoiceBankFeeAmountContainer.style.display = 'none';
                        }

                        if (invoiceMoneyReturnedContainer) {
                            if (invoiceTotalPaidValue !== undefined && invoiceTotalPaidValue > 0) {
                                invoiceMoneyReturnedContainer.style.display = 'block';
                            }
                        }

                        if (currentSelection == 'Paid') {
                            const invoiceTotalPaidInput: HTMLInputElement | null 
                                = invoiceTotalPaidContainer ? invoiceTotalPaidContainer.querySelector('input[type="text"]') : null;

                            if (invoiceTotalPaidInput) {
                                invoiceTotalPaidInput.value = '0.00';
                            }
                        }

                        break;

                    default:
                        break;
                }
            })
        }
    }

    // if (partPaymentBankFeeInput.length > 0) {
    //     partPaymentBankFeeInput.forEach(input => {
    //         const checkboxElement = input.parentElement.parentElement.parentElement.nextElementSibling;
    //         const checkbox = checkboxElement.querySelector('input[type="checkbox"]');
    //         const checkboxText = checkboxElement.querySelector('.mChkTitle');
    //         checkboxText.style.width = '120px';

    //         if (input.value == '' || input.value == '0.00') {
    //             input.style.display = 'none';
    
    //             const button = document.createElement('button');
    //             button.textContent = 'Add Bank Fee';
    //             button.setAttribute('class', 'btn btn-sm btn-danger');
    //             button.setAttribute('id', 'addBankFeeButton');
    //             button.style.width = '100%';
        
    //             // Get the parent container element of the input
    //             const container = input.closest('td');
        
    //             // Append the button to the container element
    //             container.appendChild(button);

    //             button.addEventListener('click', (e) => {
    //                 e.preventDefault();

    //                 checkbox.disabled = true;
    //                 button.style.display = 'none';
    //                 input.style.display = 'block';
    //             });
    //         }
    //     })
    // }

    /* EXISTING PART-PAYMENTS ON 'PART-PAID' AND 'PAID' INVOICES */
    if (invoicePartPaymentsContainer) {
        const partPaymentItems: NodeListOf<Element> | null 
            = invoicePartPaymentsContainer ? invoicePartPaymentsContainer.querySelectorAll('.sonata-ba-collapsed-fields') : null;

        if (partPaymentItems) {
            partPaymentItems.forEach(item => {
                let itemParent = item.parentElement ? item.parentElement.parentElement : null;

                if (!(itemParent && itemParent.id.endsWith('file'))) {
                    const bankFeeAmountInputContainer: HTMLElement | null = item.querySelector('div[id^="sonata-ba-field-"][id$="_bankFeeAmount"]');
                    const bankFeeInput: HTMLInputElement | null = item.querySelector('input[id*="_invoicePartPayments_"][id*="_bankFeeAmount"]');
                    const bankFeeNotAddedCheckbox: HTMLElement | null = item.querySelector('.checkbox');
                    const bankFeeNotAddedCheckboxInput: HTMLInputElement | null = item.querySelector('input[id$="_bankFeeNotAdded"]');
                    let checkboxText: HTMLElement | null = null;
                    
                    if (bankFeeNotAddedCheckbox) {
                        checkboxText = bankFeeNotAddedCheckbox.querySelector('.mChkTitle');
                    }
                    
                    if (checkboxText) {
                        checkboxText.style.width = '120px';
                    }
    
                    let isBankFeeAmountContainerDisplayed: boolean = true;
    
                    /* TODO: finish adding hr element */
                    // let hrElement = createHrElement();
                    // item.insertAdjacentElement('afterend', hrElement);

                    if (bankFeeInput) {
                        $(bankFeeInput).on({
                            keyup: function() {
                                formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                            },
                            blur: function() {
                                formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                            }
                        });
                    }

                    if (bankFeeNotAddedCheckboxInput) {
                        if (bankFeeNotAddedCheckboxInput.checked) {
                            isBankFeeAmountContainerDisplayed = false;
                            if (bankFeeAmountInputContainer) {
                                bankFeeAmountInputContainer.style.display = 'none';
                            }
                        } else {
                            if (bankFeeAmountInputContainer) {
                                bankFeeAmountInputContainer.style.display = 'block';
                            }
                            isBankFeeAmountContainerDisplayed = true;
                        }

                        bankFeeNotAddedCheckboxInput.addEventListener("change", function() {
                            if (bankFeeNotAddedCheckboxInput.checked) {
                                isBankFeeAmountContainerDisplayed = false;
                                if (bankFeeAmountInputContainer) {
                                    bankFeeAmountInputContainer.style.display = 'none';
                                }
                            } else {
                                if (bankFeeAmountInputContainer) {
                                    bankFeeAmountInputContainer.style.display = 'block';
                                }
                                isBankFeeAmountContainerDisplayed = true;
                            }
                        });
                    }
                }
            });
        }

        /* NEW PART PAYMENT CLICK */
        invoicePartPaymentsContainer.addEventListener('click', (e) => {
            if ((e.target as Element).matches('a[title="Add new"]'))  {
                setTimeout(() => {
                    const invoicePartPaymentItems: NodeListOf<Element> | null 
                        = invoicePartPaymentsContainer ? invoicePartPaymentsContainer.querySelectorAll('.sonata-ba-collapsed-fields') : null;

                    if (invoicePartPaymentItems) {
                        invoicePartPaymentItems.forEach((item) => {
                            let itemParent = item.parentElement ? item.parentElement.parentElement : null;
    
                            if (!(itemParent && itemParent.id.endsWith('file'))) {
                                const invoiceDatePaidInput: HTMLInputElement | null 
                                    = item.querySelector('[id*="_invoicePartPayments_"][id*="_datePaid"]');
                                const partPaymentAmountInput: HTMLInputElement | null 
                                    = item.querySelector('input[id*="_invoicePartPayments_"][id*="amount"]');
                                const partPaymentRealAmountInput: HTMLInputElement | null 
                                    = item.querySelector('input[id*="_invoicePartPayments_"][id*="realAmountPaid"]');
                                const bankFeeAmountInputContainer: HTMLElement | null 
                                    = item.querySelector('div[id^="sonata-ba-field-"][id$="_bankFeeAmount"]');
                                const partPaymentBankFeeInput: HTMLInputElement | null 
                                    = item.querySelector('input[id*="_invoicePartPayments_"][id*="_bankFeeAmount"]');
                                const bankFeeNotAddedCheckbox: HTMLElement | null = item.querySelector('.checkbox');
                                const bankFeeNotAddedCheckboxInput: HTMLInputElement | null 
                                    = item.querySelector('input[id$="_bankFeeNotAdded"]');
                                let checkboxText: HTMLElement | null = null;

                                if (bankFeeNotAddedCheckbox) {
                                    checkboxText = bankFeeNotAddedCheckbox.querySelector('.mChkTitle');
                                }

                                let isBankFeeAmountContainerDisplayed = true;

                                if (checkboxText) {
                                    checkboxText.style.width = '120px';
                                }
    
                                if (partPaymentAmountInput) {
                                    $(partPaymentAmountInput).on({
                                        keyup: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                        },
                                        blur: function() {
                                            formatCurrency($(this), "blur");
                                        }
                                    });
                                }
    
                                if (partPaymentRealAmountInput) {
                                    $(partPaymentRealAmountInput).on({
                                        keyup: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                        },
                                        blur: function() {
                                            formatCurrency($(this), "blur");
                                        }
                                    });
                                }
    
                                if (partPaymentBankFeeInput) {
                                    $(partPaymentBankFeeInput).on({
                                        keyup: function() {
                                            formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                                        },
                                        blur: function() {
                                            formatCurrency($(this), "blur");
                                        }
                                    });
                                }
    
                                if (bankFeeNotAddedCheckboxInput) {
                                    if (bankFeeNotAddedCheckboxInput.checked) {
                                        isBankFeeAmountContainerDisplayed = false;
                                        if (bankFeeAmountInputContainer) {
                                            bankFeeAmountInputContainer.style.display = 'none';
                                        }
                                    }
                                
                                    bankFeeNotAddedCheckboxInput.addEventListener("change", function() {
                                        if (bankFeeNotAddedCheckboxInput.checked) {
                                            isBankFeeAmountContainerDisplayed = false;
                                            if (bankFeeAmountInputContainer) {
                                                bankFeeAmountInputContainer.style.display = 'none';
                                            }
                                        } else {
                                            if (bankFeeAmountInputContainer) {
                                                bankFeeAmountInputContainer.style.display = 'block';
                                            }
                                            isBankFeeAmountContainerDisplayed = true;
                                        }
                                    });
                                }
                            }
                        })
                    }
                }, 1000);
            }
        });
    }

    function createHrElement() {
        let hrElement = document.createElement("hr");
        hrElement.style.borderColor = "black";

        return hrElement;
    }

    /* BUDGET CATEGORY FILTERING */
    const budgetCategoryListContainer: HTMLElement | null = document.querySelector('#budget-category-list-container');

    if (budgetCategoryListContainer) {
        const budgetMainCategoryTableBody: HTMLElement | null 
            = budgetCategoryListContainer.querySelector('#budgetMainCategories');
        const budgetMainCategoryTableTextCells: NodeListOf<Element> | null 
            = budgetMainCategoryTableBody 
            ? budgetMainCategoryTableBody.querySelectorAll('.sonata-ba-list-field.sonata-ba-list-field-string') : null;
        const budgetMainCategoryTableActionCells: NodeListOf<Element> | null 
            = budgetMainCategoryTableBody 
            ? budgetMainCategoryTableBody.querySelectorAll('.sonata-ba-list-field.sonata-ba-list-field-actions') : null;
        const subCategoriesButtons: NodeListOf<Element> | null = budgetCategoryListContainer.querySelectorAll('#sub-categories-button');

        if (budgetMainCategoryTableTextCells) {
            budgetMainCategoryTableTextCells.forEach(cell => {
                (cell as HTMLElement).style.lineHeight = "2";
            });
        }
        
        if (budgetMainCategoryTableActionCells) {
            budgetMainCategoryTableActionCells.forEach(cell => {
                (cell as HTMLElement).style.textAlign = "center";
            });
        }

        subCategoriesButtons.forEach(button => {
            button.addEventListener('click', () => {
                const mainCategoryId = (button as HTMLElement).dataset.maincategoryid;
                const mainCategoryName = (button as HTMLElement).dataset.maincategoryname;

                fetch('/get_budget_categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ mainCategoryId: mainCategoryId }), 
                })
                .then(response => response.json())
                .then(data => {
                    // console.log(data); // Handle response from the server

                    // Proceed with the standard form submission
                    updateBudgetCategoryList(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        // interface IData {
        //     // Define the properties of the objects in the array
        //     // For example:
        //     id: number;
        //     name: string;
        //     // Add more properties as needed
        // }
        
        // function updateBudgetCategoryList(data: IData[]) {
        //     // ...
        // }

        function updateBudgetCategoryList(data: any) {
            const budgetCategoriesTableBody: HTMLElement | null 
                = budgetCategoryListContainer ? budgetCategoryListContainer.querySelector('#budgetCategories') : null;

            if (budgetCategoriesTableBody) {
                budgetCategoriesTableBody.innerHTML = '';
            }

            // interface IData {
            //     id: number;
            //     name: string;
            // }
            
            // function updateBudgetCategoryList(data: IData[]) {
            //     if (data.length > 0) {
            //         data.forEach((item: IData) => {
            //             // Create a new row
            //         });
            //     }
            // }

            if (data.length > 0) {
                data.forEach((item: any) => {
                    // Create a new row
                    const newRow = document.createElement('tr');
    
                    // Name cell
                    const nameCell = document.createElement('td');
                    nameCell.textContent = item.name;
                    nameCell.style.lineHeight = "2";
    
                    // Actions cell
                    const actionsCell = document.createElement('td');
                    actionsCell.style.textAlign = 'center';
    
                    // Edit buttond
                    const editButton = document.createElement('a');
                    editButton.href = `/admin/app/budgetcategory/${item.id}/edit`;
                    editButton.className = '';
                    editButton.classList.add('btn', 'btn-sm', 'btn-default', 'edit_link');
                    editButton.title = 'Edit';
                    
                    const editButtonIcon = document.createElement('i');
                    editButtonIcon.className = '';
                    editButtonIcon.classList.add('fas', 'fa-pencil-alt');
                    editButtonIcon.setAttribute('aria-hidden', 'true');
                    
                    editButton.appendChild(editButtonIcon);
                    
                    const editText = document.createTextNode(' Edit');
                    editButton.appendChild(editText);
    
                    // Delete button
                    const deleteButton = document.createElement('a');
                    deleteButton.href = `/admin/app/budgetcategory/${item.id}/delete`;
                    deleteButton.className = '';
                    deleteButton.classList.add('btn', 'btn-sm', 'btn-default', 'delete_link');
                    deleteButton.title = 'Delete';
    
                    const deleteButtonIcon = document.createElement('i');
                    deleteButtonIcon.className = '';
                    deleteButtonIcon.classList.add('fas', 'fa-times');
                    deleteButtonIcon.setAttribute('aria-hidden', 'true');
                    
                    deleteButton.appendChild(deleteButtonIcon);
    
                    const deleteText = document.createTextNode(' Delete');
                    deleteButton.appendChild(deleteText);
    
                    // Append to Actions cell
                    actionsCell.appendChild(editButton);
                    actionsCell.appendChild(deleteButton);
    
                    // Append cells to row
                    newRow.appendChild(nameCell);
                    newRow.appendChild(actionsCell);
    
                    if (budgetCategoriesTableBody) {
                        budgetCategoriesTableBody.appendChild(newRow);
                    }
                });
            } else {
                // Create a new row
                const newRow = document.createElement('tr');
                const newCell = document.createElement('td');
                const newSpan = document.createElement('span');
                newCell.colSpan = 2;
                newCell.className = '';
                newCell.classList.add('sonata-ba-list-field', 'sonata-ba-list-field-string');
                newSpan.id = 'budget-category-list-no-results';
                newSpan.className = '';
                newSpan.classList.add('budget-category-list-no-results');
                newSpan.textContent = 'No Results - Please add a Sub Category of the Main Category.';

                // Append cells to row
                newRow.appendChild(newCell);
                newCell.appendChild(newSpan);

                if (budgetCategoriesTableBody) {
                    budgetCategoriesTableBody.appendChild(newRow);
                }
            }
        }
    }

    function getAccountCurrencyCode(invoiceAccountSelectFieldInvoice: HTMLSelectElement) {
        const regex = /\((.*?)\)/;
        let accountCurrencyCode: string | undefined;

        let selectedOptionAccount = invoiceAccountSelectFieldInvoice.options[invoiceAccountSelectFieldInvoice.selectedIndex];
        let selectedText = selectedOptionAccount.textContent;

        if (selectedText) {
            let match = selectedText.match(regex);
            if (match) {
                accountCurrencyCode = match[1];
            }
        }

        return accountCurrencyCode;
    }

    /* TODO: instantiate this from the class */
    /* Formats number 1000000 to 1,234,567 */
    function formatNumber(n: string) {
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    }

    /* Validates decimal side and puts cursor back in right position. */
    function formatCurrency(input: JQuery, blur: string | undefined) {
        // get input value
        let input_val = input.val();

        // check if input_val is undefined
        if (input_val === undefined) {
            return;
        }

        // don't validate empty input
        if (input_val === "") { 
            return; 
        }

        // ensure input_val is a string before accessing its length property
        input_val = input_val.toString();

        // original length
        let original_len = input_val.length;

        // initial caret position
        let caret_pos = input.prop("selectionStart");

        // check for decimal
        if (input_val.indexOf(".") >= 0) {

            // get position of first decimal
            // this prevents multiple decimals from
            // being entered
            let decimal_pos = input_val.indexOf(".");

            // split number by decimal point
            let left_side = input_val.substring(0, decimal_pos);
            let right_side = input_val.substring(decimal_pos);

            // add commas to left side of number
            left_side = formatNumber(left_side);

            // validate right side
            right_side = formatNumber(right_side);

            // On blur make sure 2 numbers after decimal
            if (blur === "blur") {
                right_side += "00";
            }

            // Limit decimal to only 2 digits
            right_side = right_side.substring(0, 2);

            // join number by .
            input_val = left_side + "." + right_side;

        } else {
            // no decimal entered
            // add commas to number
            // remove all non-digits
            input_val = formatNumber(input_val);

            // final formatting
            if (blur === "blur") {
                input_val += ".00";
            }
        }

        // send updated string to input
        input.val(input_val);

        // put caret back in the right position
        let updated_len = input_val.length;
        caret_pos = updated_len - original_len + caret_pos;
        (input[0] as HTMLInputElement).setSelectionRange(caret_pos, caret_pos);
    }
});
