document.addEventListener('DOMContentLoaded', function() {
    /* BANK TRANSFER/CURRENCY EXCHANGE */
    const bankTransferCreateForm: HTMLFormElement | null = document.querySelector('form[action*="/admin/bank_transfer/create"]');
    const currencyExchangeCreateForm: HTMLFormElement | null = document.querySelector('form[action*="/admin/currency_exchange/create"]');
    const cashWithdrawalCreateForm: HTMLFormElement | null = document.querySelector('form[action*="/admin/cash_withdrawal/create"]');
    const bankTransferEditForm: HTMLFormElement | null = document.querySelector('form[action^="/admin/bank_transfer/"][action*="/edit"]');
    const currencyExchangeEditForm: HTMLFormElement | null = document.querySelector('form[action^="/admin/currency_exchange/"][action*="/edit"]');
    const cashWithdrawalEditForm: HTMLFormElement | null = document.querySelector('form[action^="/admin/cash_withdrawal"][action*="/edit"]');
    const cashTransferCreateForm: HTMLFormElement | null = document.querySelector('form[action*="/admin/cash_transfer/create"]');
    const cashTransferEditForm: HTMLFormElement | null = document.querySelector('form[action*="/admin/cash_transfer/edit"]');
    const moneyReturnCreateForm: HTMLFormElement | null = document.querySelector('form[action^="/admin/money_return/"][action*="/create"]');
    const moneyReturnEditForm: HTMLFormElement | null = document.querySelector('form[action^="/admin/money_return/"][action*="/edit"]');
    const inputGroupAddonElements: NodeListOf<Element> = document.querySelectorAll('.input-group-addon');

    inputGroupAddonElements.forEach((item: Element) => {
        const htmlItem = item as HTMLElement;

        if (htmlItem.textContent !== '') {
            htmlItem.style.fontSize = '15px';
        }
    });

    if (bankTransferCreateForm || bankTransferEditForm || currencyExchangeCreateForm || currencyExchangeEditForm) {
        const transactionTypeSelectContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const transactionTypeSelect: HTMLSelectElement | null = document.querySelector('select[id$="_transactionType"]');
        const accountCurrencyContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_currency"]');
        const accountCurrencyElement: HTMLInputElement | null = document.querySelector('input[id$="_currency"]');
        const accountCurrency: string | null = accountCurrencyElement ? accountCurrencyElement.value : null;
        const transferToAccountSelect: HTMLSelectElement | null = document.querySelector('select[id$="_transferToAccount"]');
        const amountInputContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
        const newValueInputContainer: HTMLDivElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_newValue"]');
        const amountInput: HTMLInputElement | null = document.querySelector('input[id$="_amount"]');
        const newValueInput: HTMLInputElement | null = document.querySelector('input[id$="_newValue"]');
        const dateInput: HTMLDivElement | null = document.querySelector('.input-group.date');

        if (dateInput) {
            (dateInput as HTMLElement).style.width = '35%';
        }


        if (transactionTypeSelectContainer) {
            (transactionTypeSelectContainer as HTMLElement).style.display = 'none';
        }

        if (bankTransferEditForm || currencyExchangeEditForm) {
            (accountCurrencyContainer as HTMLElement).style.display = 'none';
        }

        if (amountInput && newValueInput) {
            if (!bankTransferEditForm) {
                if (currencyExchangeEditForm) {
                    let selectedOption: HTMLOptionElement | undefined;
                    let selectedAccountCurrency: string | undefined;
                    let selectedAccountCurrencySymbol: string | undefined;
                    let currencySymbolElement: HTMLElement | null = null;

                    if (transferToAccountSelect) {
                        selectedOption = transferToAccountSelect.options[transferToAccountSelect.selectedIndex] as HTMLOptionElement;
                    }

                    if (selectedOption && selectedOption.textContent) {
                        const optionText = selectedOption.textContent;
                        selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
                    }

                    if (selectedAccountCurrency) {
                        const currencyPart 
                            = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency })
                                .formatToParts(0).find(part => part.type === 'currency');
                        if (currencyPart) {
                            selectedAccountCurrencySymbol = currencyPart.value.trim();
                        }
                    }

                    if (newValueInputContainer) {
                        currencySymbolElement = newValueInputContainer.querySelector('.input-group-addon') as HTMLElement;
                    }

                    if (currencySymbolElement && selectedAccountCurrencySymbol !== undefined) {
                        currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                    }

                    (newValueInputContainer as HTMLElement).style.display = 'block';
                } else {
                    (amountInputContainer as HTMLElement).style.display = 'none';
                    (newValueInputContainer as HTMLElement).style.display = 'none';
                }
            } else {
                (newValueInputContainer as HTMLElement).style.display = 'none';
                let selectedOption: HTMLOptionElement | undefined;
                let selectedAccountCurrency: string | undefined;
                let selectedAccountCurrencySymbol: string | undefined;
                let currencySymbolElement: HTMLElement | null = null;

                if (transferToAccountSelect) {
                    selectedOption = transferToAccountSelect.options[transferToAccountSelect.selectedIndex] as HTMLOptionElement;
                }

                if (selectedOption && selectedOption.textContent) {
                    const optionText = selectedOption.textContent;
                    selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
                }

                if (selectedAccountCurrency !== accountCurrency) {
                    if (selectedAccountCurrency) {
                        const currencyPart = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency');
                        if (currencyPart) {
                            selectedAccountCurrencySymbol = currencyPart.value.trim();
                        }
                    }

                    if (newValueInputContainer) {
                        currencySymbolElement = newValueInputContainer.querySelector('.input-group-addon') as HTMLElement;
                    }

                    if (currencySymbolElement && selectedAccountCurrencySymbol !== undefined) {
                        currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                    }

                    if (newValueInputContainer) {
                        (newValueInputContainer as HTMLElement).style.display = 'block';
                    }
                }
            }
        }

        if (transferToAccountSelect) {
            $(transferToAccountSelect).on('select2:select', (e) => {
                let selectedOption: HTMLOptionElement | null = null;
                let optionText: string | null = null;
                let selectedAccountCurrency: string | undefined;
                let selectedAccountCurrencySymbol: string | undefined;
                let currencySymbolElement: HTMLElement | null = null;

                if (transferToAccountSelect) {
                    selectedOption = transferToAccountSelect.options[transferToAccountSelect.selectedIndex];
                }
                
                if (selectedOption) {
                    optionText = selectedOption.textContent;
                }

                if (optionText) {
                    selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
                }

                if (selectedAccountCurrency) {
                    const currencyPart = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency');
                    if (currencyPart) {
                        selectedAccountCurrencySymbol = currencyPart.value.trim();
                    }
                }

                if (newValueInputContainer) {
                    currencySymbolElement = newValueInputContainer.querySelector('.input-group-addon');

                    if (selectedAccountCurrency === accountCurrency) {
                        newValueInputContainer.style.display = 'none';

                        if (amountInputContainer) {
                            amountInputContainer.style.display = 'block';
                        }
                    } else {
                        if (amountInputContainer) {
                            amountInputContainer.style.display = 'block';
                        }

                        if (currencySymbolElement && selectedAccountCurrencySymbol !== undefined) {
                            currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                        }

                        newValueInputContainer.style.display = 'block';
                    }
                }
            })
        }

        /* Amount formatting */
        if (amountInput) {
            $(amountInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }

        /* New value formatting */
        if (newValueInput) {
            $(newValueInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }
    }

    if (cashWithdrawalCreateForm || cashWithdrawalEditForm) {
        const transactionTypeSelectContainer: HTMLElement | null 
            = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const accountCurrencyContainer: HTMLElement | null 
            = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_currency"]');
        const accountCurrencyInput: HTMLInputElement | null 
            = document.querySelector('input[id$="_currency"]');
        const transferFromAccountSelect: HTMLSelectElement | null 
            = document.querySelector('select[id$="_transferFromAccount"]');
        const amountInputContainer: HTMLElement | null 
            = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
        const amountFromAccountInputContainer: HTMLElement | null 
            = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amountFromAccount"]');
        const amountInput: HTMLInputElement | null = document.querySelector('input[id$="_amount"]');
        const amountFromAccountInput: HTMLInputElement | null = document.querySelector('input[id$="_amountFromAccount"]');
        const dateInput: HTMLElement | null = document.querySelector('.input-group.date');
        
        let accountCurrency: string | null = null;
        
        if (accountCurrencyInput) {
            accountCurrency = accountCurrencyInput.value;
        }

        if (dateInput) {
            dateInput.style.width = '35%';
        }

        if (transactionTypeSelectContainer) {
            transactionTypeSelectContainer.style.display = 'none';
        }

        if (accountCurrencyContainer) {
            accountCurrencyContainer.style.display = 'none';
        }

        if (cashWithdrawalCreateForm) {
            if (amountInputContainer) {
                amountInputContainer.style.display = 'none';
            }
            
            if (amountFromAccountInputContainer) {
                amountFromAccountInputContainer.style.display = 'none';
            }
        }

        if (amountInput && amountFromAccountInput) {
            if (cashWithdrawalEditForm) {
                let selectedOption: HTMLOptionElement | null = null;
                let optionText: string | null = null;
                let selectedAccountCurrency: string | undefined;
                
                if (transferFromAccountSelect) {
                    selectedOption = transferFromAccountSelect.options[transferFromAccountSelect.selectedIndex];
                }
                
                if (selectedOption) {
                    optionText = selectedOption.textContent;
                }
                
                if (optionText) {
                    selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
                }

                if (selectedAccountCurrency !== accountCurrency) {
                    let selectedAccountCurrencySymbol: string | undefined;
                    let currencySymbolElement: HTMLElement | null = null;
                    
                    if (selectedAccountCurrency) {
                        const currencyPart 
                            = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency })
                                .formatToParts(0).find(part => part.type === 'currency');
                        if (currencyPart) {
                            selectedAccountCurrencySymbol = currencyPart.value.trim();
                        }
                    }
                    
                    if (amountFromAccountInputContainer) {
                        currencySymbolElement = amountFromAccountInputContainer.querySelector('.input-group-addon');
                    }
                    
                    if (currencySymbolElement && selectedAccountCurrencySymbol !== undefined) {
                        currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                    }
                    
                    if (amountFromAccountInputContainer) {
                        amountFromAccountInputContainer.style.display = 'block';
                    }
                } else {
                    if (amountFromAccountInputContainer) {
                        amountFromAccountInputContainer.style.display = 'none';
                    }
                }
            } else {
                if (amountInputContainer) {
                    amountInputContainer.style.display = 'none';
                }
                
                if (amountFromAccountInputContainer) {
                    amountFromAccountInputContainer.style.display = 'none';
                }
            }
        }

        if (transferFromAccountSelect) {
            $(transferFromAccountSelect).on('select2:select', (e) => {
                let selectedOption: HTMLOptionElement | null = null;
                let optionText: string | null = null;
                let selectedAccountCurrency: string | undefined;
                let selectedAccountCurrencySymbol: string | undefined;
                let currencySymbolElement: HTMLElement | null = null;
                
                if (transferFromAccountSelect) {
                    selectedOption = transferFromAccountSelect.options[transferFromAccountSelect.selectedIndex];
                }
                
                if (selectedOption) {
                    optionText = selectedOption.textContent;
                }
                
                if (optionText) {
                    selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
                }
                
                if (selectedAccountCurrency) {
                    const currencyPart = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency');
                    if (currencyPart) {
                        selectedAccountCurrencySymbol = currencyPart.value.trim();
                    }
                }
                
                if (amountFromAccountInputContainer) {
                    currencySymbolElement = amountFromAccountInputContainer.querySelector('.input-group-addon');
                }
    
                if (selectedAccountCurrency === accountCurrency) {
                    if (amountFromAccountInputContainer) {
                        amountFromAccountInputContainer.style.display = 'none';
                    }
                    
                    if (amountInputContainer) {
                        amountInputContainer.style.display = 'block';
                    }
                } else {
                    if (amountInputContainer) {
                        amountInputContainer.style.display = 'block';
                    }
                    
                    if (currencySymbolElement && selectedAccountCurrencySymbol !== undefined) {
                        currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                    }
                    
                    if (amountFromAccountInputContainer) {
                        amountFromAccountInputContainer.style.display = 'block';
                    }
                }
            })
        }

        /* Amount from formatting */
        if (amountFromAccountInput) {
            $(amountFromAccountInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }

        /* Amount formatting */
        if (amountInput) {
            $(amountInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }
    }

    if (cashTransferCreateForm || cashTransferEditForm) {
        const dateInput: HTMLElement | null = document.querySelector('.input-group.date');
        const amountInput: HTMLInputElement | null = document.querySelector('input[id$="_amount"]');

        if (dateInput) {
            dateInput.style.width = '35%';
        }

        /* Amount from formatting */
        if (amountInput) {
            $(amountInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }
    }

    if (moneyReturnCreateForm || moneyReturnEditForm) {
        const transactionTypeSelectContainer: HTMLElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const invoiceSelectContainer: HTMLElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoice"]');
        const transactionSelectContainer: HTMLElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transaction"]');
        const dateInput: HTMLElement | null = document.querySelector('.input-group.date');

        if (dateInput) {
            dateInput.style.width = '35%';
        }

        if (transactionTypeSelectContainer) {
            transactionTypeSelectContainer.style.display = 'none';
        }

        if (invoiceSelectContainer) {
            invoiceSelectContainer.style.display = 'none';
        }

        if (transactionSelectContainer) {
            transactionSelectContainer.style.display = 'none';
        }
    }

    /* ADD FUNDS */
    const addFundsCreateForm: HTMLFormElement | null = document.querySelector('form[action*="/admin/add_funds/create"]');
    const addFundsEditForm: HTMLFormElement | null = document.querySelector('form[action^="/admin/add_funds/"][action*="/edit"]');
    const transactionEditForm: HTMLFormElement | null = document.querySelector('form[action^="/admin/app/transaction/"][action*="/edit"]');

    if (addFundsCreateForm || addFundsEditForm || transactionEditForm) {
        const accountSelect: HTMLSelectElement | null = document.querySelector('select[id$="_mainAccount"]');
        const transactionTypeSelectContainer: HTMLElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const bankFeeAmountInputContainer: HTMLElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_bankFeeAmount"]');
        const bankFeeAmountInput: HTMLInputElement | null = document.querySelector('input[id$="_bankFeeAmount"]');
        const bankFeeNotAddedCheckbox: HTMLInputElement | null = document.querySelector('input[id$="_bankFeeNotAdded"]');
        const amountInputContainer: HTMLElement | null = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
        const amountInput: HTMLInputElement | null = document.querySelector('input[id$="_amount"]');
        const dateInput: HTMLElement | null = document.querySelector('.input-group.date');
        let amountCurrencySymbolElement: HTMLElement | null = null;
        let bankFeeCurrencySymbolElement: HTMLElement | null = null;
        let isBankFeeAmountContainerDisplayed: boolean;

        if (amountInputContainer) {
            amountCurrencySymbolElement = amountInputContainer.querySelector('.input-group-addon');
        }
        
        if (bankFeeAmountInputContainer) {
            bankFeeCurrencySymbolElement = bankFeeAmountInputContainer.querySelector('.input-group-addon');
        }

        if (dateInput) {
            dateInput.style.width = '35%';
        }

        if (transactionTypeSelectContainer) {
            transactionTypeSelectContainer.style.display = 'none';
        }

        if (addFundsCreateForm) {
            if (bankFeeAmountInputContainer) {
                bankFeeCurrencySymbolElement = bankFeeAmountInputContainer.querySelector('.input-group-addon');
            }

            if (bankFeeCurrencySymbolElement) {
                bankFeeCurrencySymbolElement.textContent = '';
            }
        }

        if (amountCurrencySymbolElement) {
            if (addFundsCreateForm) {
                amountCurrencySymbolElement.textContent = '';
            }
        }

		if (bankFeeNotAddedCheckbox) {
			if (bankFeeNotAddedCheckbox.checked == true) {
				isBankFeeAmountContainerDisplayed = false;

				if (bankFeeAmountInputContainer) {
                    bankFeeAmountInputContainer.style.display = 'none';
                }
			} else {
				isBankFeeAmountContainerDisplayed = true;
			}
		}

        if (accountSelect) {
            $(accountSelect).on('select2:select', (e) => {
                let selectedAccountCurrency: string | null = getAccountCurrencyCode(accountSelect);
    
                const currencySymbolPart 
                    = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency })
                        .formatToParts(0).find(part => part.type === 'currency');
        
                if (currencySymbolPart) {
                    const currencySymbol = currencySymbolPart.value.trim();
        
                    if (bankFeeCurrencySymbolElement) {
                        bankFeeCurrencySymbolElement.textContent = currencySymbol;
                    }
        
                    if (amountCurrencySymbolElement) {
                        amountCurrencySymbolElement.textContent = currencySymbol;
                    }
                }
            });
        }

		if (bankFeeNotAddedCheckbox) {
			bankFeeNotAddedCheckbox.addEventListener('change', function () {
				if (bankFeeNotAddedCheckbox.checked) {
					if (bankFeeAmountInputContainer) {
                        bankFeeAmountInputContainer.style.display = 'none';
                    }
				} else {
					if (bankFeeAmountInputContainer) {
                        bankFeeAmountInputContainer.style.display = 'block';
                    }
				}
			});
		}

        if (amountInput) {
            $(amountInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }

        if (bankFeeAmountInput) {
            $(bankFeeAmountInput).on({
                keyup: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), '');
                },
                blur: function() {
                    formatCurrency($(this as unknown as JQuery<HTMLElement>), "blur");
                }
            });
        }
    }

    function getAccountCurrencyCode(selectedAccount) {
        const regex = /\((.*?)\)/;

        let selectedOptionAccount = selectedAccount.options[selectedAccount.selectedIndex];
        let selectedText = selectedOptionAccount.textContent;
        let currencyCode = '';

        if (selectedText.match(regex)) {
            currencyCode = selectedText.match(regex)[1];
        }

        return currencyCode;
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