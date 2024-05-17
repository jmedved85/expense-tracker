$(function() {
    /* INVOICE CREATE FORM - currency symbol on Amount total invoice field */
    const invoiceCreateForm = document.querySelector('form[action*="/admin/app/invoice/"][action*="/create"]');
    const invoiceAddNewCreateForm = document.querySelector('form[action^="/admin/app/invoice/create"]');
    const invoiceEditForm = document.querySelector('form[action^="/admin/app/invoice"][action*="/edit"]');
    
    if (invoiceAddNewCreateForm || invoiceCreateForm || invoiceEditForm) {
        const invoiceAccountSelectFieldInvoice = document.querySelector('select[id$="_account"]');
        const currencySelectFieldInvoice = document.querySelector('select[id$="_currency"]');
        const currencyTextFieldInvoice = document.querySelector('input[id$="_currency"]');
        const amountTotalInvoiceContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
        const realAmountPaidInvoiceContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_realAmountPaid"]');
        const bankFeeInvoiceContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_bankFeeAmount"]');
        const invoiceLinesContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoiceLines"]');
        const budgetMainCategorySelect = document.querySelector('select[id$="_budgetMainCategory"]');
        const invoiceDateInput = document.querySelectorAll('.input-group.date');
        // const addNewButtonInvoiceLines = invoiceLinesContainer.querySelector('a[title="Add new"]');

        invoiceDateInput.forEach(item => {
            item.style.width = '35%';
        });

        let accountCurrencyCode;
        let selectedCurrencyCode;
        let mainCategoryId;

        if (invoiceAccountSelectFieldInvoice && currencySelectFieldInvoice && budgetMainCategorySelect) {
            if (invoiceAccountSelectFieldInvoice === '' || currencySelectFieldInvoice.value === '') {
                if (amountTotalInvoiceContainer) {
                    const currencySymbolElementAmountTotal = amountTotalInvoiceContainer.querySelector('.input-group-addon');
                    currencySymbolElementAmountTotal.textContent = '\u00A0\u00A0';
                }
        
                if (realAmountPaidInvoiceContainer) {
                    const currencySymbolElementRealAmountPaid = realAmountPaidInvoiceContainer.querySelector('.input-group-addon');
                    currencySymbolElementRealAmountPaid.textContent = '\u00A0\u00A0';
                }
    
                if (bankFeeInvoiceContainer) {
                    const currencySymbolElementBankFeeAmount = bankFeeInvoiceContainer.querySelector('.input-group-addon');
                    currencySymbolElementBankFeeAmount.textContent = '\u00A0\u00A0';
                }
            }
        }

        $(invoiceAccountSelectFieldInvoice).on('select2:select', (e) => {
            const currencySymbolElementRealAmountPaid = realAmountPaidInvoiceContainer.querySelector('.input-group-addon');
            const currencySymbolElementBankFeeAmount = bankFeeInvoiceContainer.querySelector('.input-group-addon');
            const invoiceRealAmountPaidContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_realAmountPaid"]');
            const realAmountPaidInvoiceInput = realAmountPaidInvoiceContainer.querySelector('input[id$="_realAmountPaid"]');
            const invoicePaymentStatusSelect = document.querySelector('select[id$="_invoicePaymentStatus"]');
            const regex = /\((.*?)\)/;
            let selectedOption = e.params.data;
            const matches = selectedOption.text.match(regex);

            /* TODO: fix Add New Invoice Paying bank Account select "Real Amount Paid" currency symbol */

            if (matches && matches.length > 1) {
                accountCurrencyCode = matches[1];
                const currencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: accountCurrencyCode }).formatToParts(0).find(part => part.type === 'currency').value.trim();

                if (currencySymbolElementRealAmountPaid) {
                    currencySymbolElementRealAmountPaid.textContent = currencySymbol;
                }

                currencySymbolElementBankFeeAmount.textContent = currencySymbol;
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
                    realAmountPaidInvoiceContainer.style.display = 'none';
                    realAmountPaidInvoiceInput.required = false;
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

        $(currencySelectFieldInvoice).on('select2:select', (e) => {
            if (realAmountPaidInvoiceContainer && amountTotalInvoiceContainer) {
                const invoicePaymentStatusSelect = document.querySelector('select[id$="_invoicePaymentStatus"]');
                const realAmountPaidInvoiceInput = realAmountPaidInvoiceContainer.querySelector('input[id$="_realAmountPaid"]');
                const currencySymbolElementAmountTotal = amountTotalInvoiceContainer.querySelector('.input-group-addon');
                const selectedOption = currencySelectFieldInvoice.options[currencySelectFieldInvoice.selectedIndex];
                selectedCurrencyCode = selectedOption.value;

                const currencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: selectedCurrencyCode }).formatToParts(0).find(part => part.type === 'currency').value.trim();

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

        /* Amount (Invoice Lines Total) calculation && Budget Category filtration */
        let invoiceAmountTotal = amountTotalInvoiceContainer.querySelector('input[id$="_amount"]');
        let selectedBudgetCategories = [];

        invoiceLinesContainer.addEventListener('click', (e) => {
            if (e.target.matches('a[title="Add new"]')) {
                /* INVOICE TOTAL SUM CALCULATION */
                let lineTotalsSum = 0;

                const lineTotals = invoiceLinesContainer.querySelectorAll('input[id$="_lineTotal"]');

                lineTotals.forEach((lineTotal) => {
                    let lineTotalValue = lineTotal.value.replace(/,/g, '');
                    let lineTotalValueFloat = parseFloat(lineTotalValue);

                    if (!isNaN(lineTotalValueFloat)) {
                        lineTotalsSum += lineTotalValueFloat;
                    }
                });

                const formattedSum = lineTotalsSum.toFixed(2);

                invoiceAmountTotal.value = formattedSum;

                const invoiceLineItems = invoiceLinesContainer.querySelectorAll('.ui-sortable-handle');

                invoiceLineItems.forEach((item) => {
                    const budgetMainCategorySelect = item.querySelector('select[id*="_budgetMainCategory"]');
                    const budgetCategorySelect = item.querySelector('select[id*="_budgetCategory"]');

                    if (budgetMainCategorySelect.selectedIndex > 0) {
                        const categoryElementId = budgetCategorySelect.id;
                        const categoryOptions = budgetCategorySelect.querySelectorAll('option');
                        const optionsArr = [];

                        categoryOptions.forEach((option) => {
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
                    const invoiceLinesRows = invoiceLinesContainer.querySelector('.sonata-ba-tbody.ui-sortable')
                        ?? invoiceLinesContainer.querySelector('.sonata-ba-collapsed-fields');

                    if (invoiceLinesRows) {
                        const invoiceLineTableStyleItems = invoiceLinesContainer.querySelectorAll('.ui-sortable-handle');
                        const invoiceLineFormStyleItems = invoiceLinesContainer.querySelectorAll('fieldset');
        
                        const invoiceLineItems = invoiceLineTableStyleItems.length > 0 ? invoiceLineTableStyleItems : invoiceLineFormStyleItems;

                        invoiceLineItems.forEach((item) => {
                            let itemParent = item.parentElement;

                            if (!(itemParent && itemParent.id.endsWith('file'))) {
                                const budgetMainCategorySelect = item.querySelector('select[id*="_budgetMainCategory"]');
                                const budgetCategorySelect = item.querySelector('select[id*="_budgetCategory"]');
                                const netValueInput = item.querySelector('input[id*="_netValue"]');
                                const vatInput = item.querySelector('input[id*="_vat"]');
                                const vatValueInput = item.querySelector('input[id*="_vatValue"]');
                                const lineTotalInput = item.querySelector('input[id*="_lineTotal"]');

                                if (Array.isArray(selectedBudgetCategories)) {
                                    if (selectedBudgetCategories.length > 0) {
                                        selectedBudgetCategories.forEach((selectedCategory) => {
                                            if (budgetMainCategorySelect.selectedIndex > 0) {
                                                if (budgetCategorySelect.id == selectedCategory.key) {
                                                    updateBudgetCategoryList(budgetCategorySelect, selectedCategory.value);
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
                                        updateBudgetCategoryList(budgetCategorySelect, data);
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                    });
                                });

                                $(netValueInput).on({
                                    keyup: function() {
                                        formatCurrency($(this));
                                    },
                                    blur: function() {
                                        formatCurrency($(this), "blur");
                                    }
                                });

                                $(vatInput).on({
                                    keyup: function() {
                                        formatCurrency($(this));
                                    },
                                    blur: function() {
                                        formatCurrency($(this), "blur");
                                    }
                                });

                                $(vatValueInput).on({
                                    keyup: function() {
                                        formatCurrency($(this));
                                    },
                                    blur: function() {
                                        formatCurrency($(this), "blur");
                                    }
                                });

                                $(lineTotalInput).on({
                                    keyup: function() {
                                        formatCurrency($(this));
                                    },
                                    blur: function() {
                                        formatCurrency($(this), "blur");
                                    }
                                });
                            }
                        });

                        clearInterval(interval);
                    }
                }

                const interval = setInterval(checkForInvoiceLineRowToOpen, 1200);
            }
        });

        if (invoiceEditForm) {
            const invoiceLinesRows = invoiceLinesContainer.querySelector('.sonata-ba-tbody.ui-sortable')
            ?? invoiceLinesContainer.querySelector('.sonata-ba-collapsed-fields');

            if (invoiceLinesRows) {
                const invoiceLineTableStyleItems = invoiceLinesContainer.querySelectorAll('.ui-sortable-handle');
                const invoiceLineFormStyleItems = invoiceLinesContainer.querySelectorAll('fieldset');
                const invoiceLineItems = invoiceLineTableStyleItems.length > 0 ? invoiceLineTableStyleItems : invoiceLineFormStyleItems;

                invoiceLineItems.forEach((item) => {
                    let itemParent = item.parentElement;

                    if (!(itemParent && itemParent.id.endsWith('file'))) {
                        const budgetMainCategorySelect = item.querySelector('select[id*="_budgetMainCategory"]');
                        const budgetCategorySelect = item.querySelector('select[id*="_budgetCategory"]');
                        const netValueInput = item.querySelector('input[id*="_netValue"]');
                        const vatInput = item.querySelector('input[id*="_vat"]');
                        const vatValueInput = item.querySelector('input[id*="_vatValue"]');
                        const lineTotalInput = item.querySelector('input[id*="_lineTotal"]');

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
                                updateBudgetCategoryList(budgetCategorySelect, data);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                        });

                        $(netValueInput).on({
                            keyup: function() {
                                formatCurrency($(this));
                            },
                            blur: function() {
                                formatCurrency($(this), "blur");
                            }
                        });
    
                        $(vatInput).on({
                            keyup: function() {
                                formatCurrency($(this));
                            },
                            blur: function() {
                                formatCurrency($(this), "blur");
                            }
                        });
    
                        $(vatValueInput).on({
                            keyup: function() {
                                formatCurrency($(this));
                            },
                            blur: function() {
                                formatCurrency($(this), "blur");
                            }
                        });
    
                        $(lineTotalInput).on({
                            keyup: function() {
                                formatCurrency($(this));
                            },
                            blur: function() {
                                formatCurrency($(this), "blur");
                            }
                        });
                    }
                });
            }
        }

        function updateBudgetCategoryList(budgetCategorySelect, filteredBudgetCategories) {
            budgetCategorySelect.innerHTML = '';

            if (filteredBudgetCategories) {
                const newBudgetCategorySelect = document.createElement('select');
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

    /* INVOICE DOWNPAYMENTS && MONEY RETURNED CONTAINER DISPLAY ON/OFF by Payment Status 'Part-Paid' && 'Money Returned' selector */
    // const invoiceCreateForm = document.querySelector('form[action*="/admin/app/invoice/"]');
    // const invoiceCurrencyContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_currency"]');
    const invoiceAccountContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_account"]');
    const invoicePaymentStatusSelect = document.querySelector('select[id$="_invoicePaymentStatus"]');
    const invoicePartPaymentsContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoicePartPayments"]');
    const invoiceDatePaidContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoiceDatePaid"]');
    const invoiceRealAmountPaidContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_realAmountPaid"]');
    const invoiceBankFeeAmountContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_bankFeeAmount"]');
    const bankFeeAmountInputHelp = document.querySelector('p[id$="_bankFeeAmount_help"]');
    const invoicePartPaymentsElementFieldWidget = document.querySelector('span[id^="field_widget_"][id$="_invoicePartPayments"]');
    const invoiceAmountContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
    const invoiceTotalPaidContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_totalPaid"]');
    const partPaymentDatePaidInput = document.querySelectorAll('input[id*="_invoicePartPayments_"][id*="_datePaid"]');
    const partPaymentAmountInput = document.querySelectorAll('input[id*="_invoicePartPayments_"][id*="_amount"]');
    const partPaymentBankFeeInput = document.querySelectorAll('input[id*="_invoicePartPayments_"][id*="_bankFeeAmount"]');
    const invoiceAccountSelectFieldInvoice = document.querySelector('select[id$="_account"]');
    const currencySelectFieldInvoice = document.querySelector('select[id$="_currency"]');
    const currencyTextFieldInvoice = document.querySelector('input[id$="_currency"]');
    let invoiceMoneyReturnedContainer;

    let colMd6Elements = document.getElementsByClassName("col-md-6");

    if (colMd6Elements) {
        for (let i = 0; i < colMd6Elements.length; i++) {
            let h4Element = colMd6Elements[i].querySelector(".box-title");
            if (h4Element && h4Element.textContent.trim() === "Money Returns") {
                invoiceMoneyReturnedContainer = colMd6Elements[i];
            }
        }
    }

    let accountCurrencyCode = "";
    let selectedCurrencyCode = "";

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

    let bankFeeAmountInput = null;
    let bankFeeAmountInputLabel = null;

    if (invoiceBankFeeAmountContainer) {
        bankFeeAmountInput = invoiceBankFeeAmountContainer.querySelector('input[type="text"]');
        bankFeeAmountInputLabel = invoiceBankFeeAmountContainer.querySelector('label[for$="_bankFeeAmount"]');

        /* Amount formatting */
        $(bankFeeAmountInput).on({
            keyup: function() {
                formatCurrency($(this));
            },
            blur: function() {
                formatCurrency($(this), "blur");
            }
        });
    }

    if (invoiceRealAmountPaidContainer) {
        realAmountPaidInput = invoiceRealAmountPaidContainer.querySelector('input[type="text"]');

        /* Amount formatting */
        $(realAmountPaidInput).on({
            keyup: function() {
                formatCurrency($(this));
            },
            blur: function() {
                formatCurrency($(this), "blur");
            }
        });
    }

    if (invoicePaymentStatusSelect) {
        const invoiceAccountSelect = invoiceAccountContainer.querySelector('select[id$="_account"]');
        const realAmountPaidInvoiceInput = invoiceRealAmountPaidContainer.querySelector('input[id$="_realAmountPaid"]');

        /* NOTE: Temporary solution */
        if (invoicePaymentStatusSelect.value === 'Paid' && invoicePartPaymentsContainer) {
            const partPaidSelectOption = invoicePaymentStatusSelect.querySelector('option[value="Part-Paid"]');
            invoiceRealAmountPaidContainer.style.display = 'none';
            partPaidSelectOption.remove();
    
            const partPaymentAddNewButton = invoicePartPaymentsContainer.querySelector('.sonata-ba-action');
            partPaymentAddNewButton.style.display = 'none';
    
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
            const paidSelectOption = invoicePaymentStatusSelect.querySelector('option[value="Paid"]');
            paidSelectOption.remove();
        }

        if (invoicePaymentStatusSelect.value == 'Unpaid') {
            invoiceAccountContainer.style.display = 'none';
            invoiceAccountSelect.required = false;
            invoicePartPaymentsContainer.style.display = 'none';
            invoiceTotalPaidContainer.style.display = 'none';
            invoiceDatePaidContainer.style.display = 'none';
            invoiceRealAmountPaidContainer.style.display = 'none';
            invoiceBankFeeAmountContainer.style.display = 'none';
        } else if (invoicePaymentStatusSelect.value == 'Paid') {
            invoicePartPaymentsContainer.style.display = 'none';
            invoiceAccountContainer.style.display = 'block';
            invoiceAccountSelect.required = true;

            if (invoicePartPaymentsElementFieldWidget) {
                if (invoicePartPaymentsElementFieldWidget.textContent.trim() !== "") {
                    partPartPaymentsHasData = true;
                    invoicePartPaymentsContainer.style.display = 'block';
                    if (bankFeeAmountInput && bankFeeAmountInputLabel) {
                        bankFeeAmountInput.disabled = true;
                        bankFeeAmountInputLabel.textContent = 'Total Bank Fee';
                        // bankFeeAmountInputHelp.textContent = 'Total Bank Fee paid for the Part-Payment transactions';
                    }
                }
            }
    
            invoiceTotalPaidContainer.style.display = 'none';
            invoiceDatePaidContainer.style.display = 'block';

            if (accountCurrencyCode == selectedCurrencyCode) {
                invoiceRealAmountPaidContainer.style.display = 'none';
                realAmountPaidInvoiceInput.required = false;
            } else {
                invoiceRealAmountPaidContainer.style.display = 'block';
                realAmountPaidInvoiceInput.required = true;
            }
        } else {
            invoiceAccountContainer.style.display = 'block';
            invoiceAccountSelect.required = true;
            invoicePartPaymentsContainer.style.display = 'block';
            invoiceTotalPaidContainer.style.display = 'block';
            invoiceDatePaidContainer.style.display = 'none';
            if (accountCurrencyCode == selectedCurrencyCode) {
                invoiceRealAmountPaidContainer.style.display = 'none';
            }

            if (invoicePartPaymentsElementFieldWidget) {
                if (invoicePartPaymentsElementFieldWidget.textContent.trim() !== "") {
                    invoiceBankFeeAmountContainer.style.display = 'block';
                    if (bankFeeAmountInput && bankFeeAmountInputLabel) {
                        bankFeeAmountInput.disabled = true;
                        bankFeeAmountInputLabel.textContent = 'Total Bank Fee';
                        // bankFeeAmountInputHelp.textContent = 'Total Bank Fee paid for the Part-Payment transactions';
                    }
                }
            } else {
                invoiceBankFeeAmountContainer.style.display = 'none';
            }
        }
    }

    if (invoicePartPaymentsContainer) {
        if (invoicePaymentStatusSelect) {
            let currentSelection = invoicePaymentStatusSelect.value;
            const invoiceAccountSelect = invoiceAccountContainer.querySelector('select[id$="_account"]');
            // const invoiceDatePaidInput = invoiceDatePaidContainer.querySelector('input[id$="_invoiceDatePaid"]');
            // const invoiceRealAmountPaidInput = invoiceRealAmountPaidContainer.querySelector('input[id$="_realAmountPaid"]');

            let invoiceAmountValue;
            let invoiceTotalPaidValue;
            
            if (invoiceAmountContainer) {
                invoiceAmountValue = parseFloat(invoiceAmountContainer.querySelector('input[id$="_amount"]').value);
            }

            if (invoiceTotalPaidContainer) {
                invoiceTotalPaidValue = parseFloat(invoiceTotalPaidContainer.querySelector('input[id$="_totalPaid"]').value);
            }

            $(invoicePaymentStatusSelect).on('select2:select', (e) => {
                let selectedOption = e.params.data;
    
                switch (selectedOption.id) {
                    case 'Unpaid':
                        invoiceAccountContainer.style.display = 'none';
                        invoiceAccountSelect.required = false;
                        invoicePartPaymentsContainer.style.display = 'none';
                        invoiceDatePaidContainer.style.display = 'none';
                        invoiceRealAmountPaidContainer.style.display = 'none';
                        invoiceBankFeeAmountContainer.style.display = 'none';
                        invoiceTotalPaidContainer.style.display = 'none';

                        if (invoiceMoneyReturnedContainer) {
                            invoiceMoneyReturnedContainer.style.display = 'none';
                        }

                        break;
                    case 'Paid':
                        invoiceAccountContainer.style.display = 'block';
                        invoiceAccountSelect.required = true;
                        invoiceDatePaidContainer.style.display = 'block';
                        // invoiceRealAmountPaidContainer.style.display = 'block';
                        invoicePartPaymentsContainer.style.display = 'none';
                        invoiceBankFeeAmountContainer.style.display = 'block';

                        if (currentSelection == 'Part-Paid') {
                            invoiceTotalPaidContainer.style.display = 'none';
                            bankFeeAmountInput.disabled = false;
                            // bankFeeAmountInput.value = '0.00';
                            bankFeeAmountInputLabel.textContent = 'Bank Fee';
                            // bankFeeAmountInputHelp.textContent = "Insert value in 'X,XXX.XX' format";
                        }

                        if (invoicePartPaymentsElementFieldWidget) {
                            if (currentSelection !== 'Part-Paid') {
                                if (invoicePartPaymentsElementFieldWidget.textContent.trim() !== "") {
                                    invoiceTotalPaidContainer.style.display = 'block';
                                } else {
                                    invoiceTotalPaidContainer.style.display = 'none';
                                }
                            }
                        }

                        if (invoiceAmountValue == invoiceTotalPaidValue) {
                            invoiceMoneyReturnedContainer.style.display = 'block';
                        }
                        
                        break;
                    case 'Part-Paid':
                        invoiceAccountContainer.style.display = 'block';
                        invoiceAccountSelect.required = true;
                        invoicePartPaymentsContainer.style.display = 'block';
                        invoiceTotalPaidContainer.style.display = 'block';
                        invoiceDatePaidContainer.style.display = 'none';
                        invoiceRealAmountPaidContainer.style.display = 'none';
                        invoiceBankFeeAmountContainer.style.display = 'none';

                        if (invoiceMoneyReturnedContainer) {
                            if (invoiceTotalPaidValue > 0) {
                                invoiceMoneyReturnedContainer.style.display = 'block';
                            }
                        }

                        if (currentSelection == 'Paid') {
                            const invoiceTotalPaidInput = invoiceTotalPaidContainer.querySelector('input[type="text"]');
                            invoiceTotalPaidInput.value = '0.00';
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
        const partPaymentItems = invoicePartPaymentsContainer.querySelectorAll('.sonata-ba-collapsed-fields');

        partPaymentItems.forEach(item => {
            let itemParent = item.parentElement.parentElement;

            if (!(itemParent && itemParent.id.endsWith('file'))) {
                const bankFeeAmountInputContainer = item.querySelector('div[id^="sonata-ba-field-"][id$="_bankFeeAmount"]');
                const bankFeeInput = item.querySelector('input[id*="_invoicePartPayments_"][id*="_bankFeeAmount"]');
                const bankFeeNotAddedCheckbox = item.querySelector('.checkbox');
                const bankFeeNotAddedCheckboxInput = item.querySelector('input[id$="_bankFeeNotAdded"]');
                const checkboxText = bankFeeNotAddedCheckbox.querySelector('.mChkTitle');
                checkboxText.style.width = '120px';

                let isBankFeeAmountContainerDisplayed = true;

                /* TODO: finish adding hr element */
                // let hrElement = createHrElement();
                // item.insertAdjacentElement('afterend', hrElement);

                /* Amount formatting */
                $(bankFeeInput).on({
                    keyup: function() {
                        formatCurrency($(this));
                    },
                    blur: function() {
                        formatCurrency($(this), "blur");
                    }
                });
    
                if (bankFeeNotAddedCheckboxInput.checked) {
                    isBankFeeAmountContainerDisplayed = false;
                    bankFeeAmountInputContainer.style.display = 'none';
                } else {
                    bankFeeAmountInputContainer.style.display = 'block';
                    isBankFeeAmountContainerDisplayed = true;
                }

                bankFeeNotAddedCheckboxInput.addEventListener("change", function() {
                    if (bankFeeNotAddedCheckboxInput.checked) {
                        isBankFeeAmountContainerDisplayed = false;
                        bankFeeAmountInputContainer.style.display = 'none';
                    } else {
                        bankFeeAmountInputContainer.style.display = 'block';
                        isBankFeeAmountContainerDisplayed = true;
                    }
                });
            }
        });

        /* NEW PART PAYMENT CLICK */
        invoicePartPaymentsContainer.addEventListener('click', (e) => {
            if (e.target.matches('a[title="Add new"]')) {
                setTimeout(() => {
                    const invoicePartPaymentItems = invoicePartPaymentsContainer.querySelectorAll('.sonata-ba-collapsed-fields');

                    invoicePartPaymentItems.forEach((item) => {
                        let itemParent = item.parentElement.parentElement;

                        if (!(itemParent && itemParent.id.endsWith('file'))) {
                            const invoiceDatePaidInput = item.querySelector('[id*="_invoicePartPayments_"][id*="_datePaid"]');
                            const partPaymentAmountInput = item.querySelector('input[id*="_invoicePartPayments_"][id*="amount"]');
                            const partPaymentRealAmountInput = item.querySelector('input[id*="_invoicePartPayments_"][id*="realAmountPaid"]');
                            const bankFeeAmountInputContainer = item.querySelector('div[id^="sonata-ba-field-"][id$="_bankFeeAmount"]');
                            const partPaymentBankFeeInput = item.querySelector('input[id*="_invoicePartPayments_"][id*="_bankFeeAmount"]');
                            const bankFeeNotAddedCheckbox = item.querySelector('.checkbox');
                            const bankFeeNotAddedCheckboxInput = item.querySelector('input[id$="_bankFeeNotAdded"]');
                            const checkboxText = bankFeeNotAddedCheckbox.querySelector('.mChkTitle');

                            let isBankFeeAmountContainerDisplayed = true;

                            // invoiceDatePaidInput.style.width = '35%';
                            checkboxText.style.width = '120px';

                            $(partPaymentAmountInput).on({
                                keyup: function() {
                                    formatCurrency($(this));
                                },
                                blur: function() {
                                    formatCurrency($(this), "blur");
                                }
                            });

                            $(partPaymentRealAmountInput).on({
                                keyup: function() {
                                    formatCurrency($(this));
                                },
                                blur: function() {
                                    formatCurrency($(this), "blur");
                                }
                            });

                            $(partPaymentBankFeeInput).on({
                                keyup: function() {
                                    formatCurrency($(this));
                                },
                                blur: function() {
                                    formatCurrency($(this), "blur");
                                }
                            });

                            if (bankFeeNotAddedCheckboxInput.checked) {
                                isBankFeeAmountContainerDisplayed = false;
                                bankFeeAmountInputContainer.style.display = 'none';
                            }

                            bankFeeNotAddedCheckboxInput.addEventListener("change", function() {
                                if (bankFeeNotAddedCheckboxInput.checked) {
                                    isBankFeeAmountContainerDisplayed = false;
                                    bankFeeAmountInputContainer.style.display = 'none';
                                } else {
                                    bankFeeAmountInputContainer.style.display = 'block';
                                    isBankFeeAmountContainerDisplayed = true;
                                }
                            });
                        }
                    })
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
    const budgetCategoryListContainer = document.querySelector('#budget-category-list-container');

    if (budgetCategoryListContainer) {
        const budgetMainCategoryTableBody = budgetCategoryListContainer.querySelector('#budgetMainCategories');
        const budgetMainCategoryTableTextCells = budgetMainCategoryTableBody.querySelectorAll('.sonata-ba-list-field.sonata-ba-list-field-string');
        const budgetMainCategoryTableActionCells = budgetMainCategoryTableBody.querySelectorAll('.sonata-ba-list-field.sonata-ba-list-field-actions');
        const subCategoriesButtons = budgetCategoryListContainer.querySelectorAll('#sub-categories-button');

        budgetMainCategoryTableTextCells.forEach(cell => {
            cell.style.lineHeight = "2";
        });

        budgetMainCategoryTableActionCells.forEach(cell => {
            cell.style.textAlign = "center";
        });

        subCategoriesButtons.forEach(button => {
            button.addEventListener('click', () => {
                const mainCategoryId = button.dataset.maincategoryid;
                const mainCategoryName = button.dataset.maincategoryname;

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

        function updateBudgetCategoryList(data) {
            const budgetCategoriesTableBody = budgetCategoryListContainer.querySelector('#budgetCategories');
            budgetCategoriesTableBody.innerHTML = '';

            if (data.length > 0) {
                data.forEach(item => {
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
                    editButton.classList = 'btn btn-sm btn-default edit_link';
                    editButton.title = 'Edit';
                    
                    const editButtonIcon = document.createElement('i');
                    editButtonIcon.classList = 'fas fa-pencil-alt';
                    editButtonIcon.setAttribute('aria-hidden', 'true');
                    
                    editButton.appendChild(editButtonIcon);
                    
                    const editText = document.createTextNode(' Edit');
                    editButton.appendChild(editText);
    
                    // Delete button
                    const deleteButton = document.createElement('a');
                    deleteButton.href = `/admin/app/budgetcategory/${item.id}/delete`;
                    deleteButton.classList = 'btn btn-sm btn-default delete_link';
                    deleteButton.title = 'Delete';
    
                    const deleteButtonIcon = document.createElement('i');
                    deleteButtonIcon.classList = 'fas fa-times';
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
    
                    budgetCategoriesTableBody.appendChild(newRow);
                });
            } else {
                // Create a new row
                const newRow = document.createElement('tr');
                const newCell = document.createElement('td');
                const newSpan = document.createElement('span');
                newCell.colSpan = "2";
                newCell.classList = 'sonata-ba-list-field sonata-ba-list-field-string';
                newSpan.id = 'budget-category-list-no-results';
                newSpan.classList = 'budget-category-list-no-results';
                newSpan.textContent = 'No Results - Please add a Sub Category of the Main Category.';

                // Append cells to row
                newRow.appendChild(newCell);
                newCell.appendChild(newSpan);

                budgetCategoriesTableBody.appendChild(newRow);
            }
        }
    }


    function getAccountCurrencyCode(invoiceAccountSelectFieldInvoice) {
        const regex = /\((.*?)\)/;

        let selectedOptionAccount = invoiceAccountSelectFieldInvoice.options[invoiceAccountSelectFieldInvoice.selectedIndex];
        let selectedText = selectedOptionAccount.textContent;

        if (selectedText.match(regex)) {
            accountCurrencyCode = selectedText.match(regex)[1];
        }

        return accountCurrencyCode;
    }

    /* TODO: instantiate this from the class */
    /* Formats number 1000000 to 1,234,567 */
    function formatNumber(n) {
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    }

    /* Validates decimal side and puts cursor back in right position. */
    function formatCurrency(input, blur) {
        // get input value
        let input_val = input.val();

        // don't validate empty input
        if (input_val === "") { return; }

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
        input[0].setSelectionRange(caret_pos, caret_pos);
    }
});
