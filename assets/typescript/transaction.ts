document.addEventListener('DOMContentLoaded', function() {
    /* BANK TRANSFER/CURRENCY EXCHANGE */
    const bankTransferCreateForm = document.querySelector('form[action*="/admin/bank_transfer/create"]');
    const currencyExchangeCreateForm = document.querySelector('form[action*="/admin/currency_exchange/create"]');
    const cashWithdrawalCreateForm = document.querySelector('form[action*="/admin/cash_withdrawal/create"]');
    const bankTransferEditForm = document.querySelector('form[action^="/admin/bank_transfer/"][action*="/edit"]');
    const currencyExchangeEditForm = document.querySelector('form[action^="/admin/currency_exchange/"][action*="/edit"]');
    const cashWithdrawalEditForm = document.querySelector('form[action^="/admin/cash_withdrawal"][action*="/edit"]');
    const cashTransferCreateForm = document.querySelector('form[action*="/admin/cash_transfer/create"]');
    const cashTransferEditForm = document.querySelector('form[action*="/admin/cash_transfer/edit"]');
    const moneyReturnCreateForm = document.querySelector('form[action^="/admin/money_return/"][action*="/create"]');
    const moneyReturnEditForm = document.querySelector('form[action^="/admin/money_return/"][action*="/edit"]');
    const inputGroupAddonElements = document.querySelectorAll('.input-group-addon');

    inputGroupAddonElements.forEach((item: Element) => {
        const htmlItem = item as HTMLElement;

        if (htmlItem.textContent !== '') {
            htmlItem.style.fontSize = '15px';
        }
    });

    if (bankTransferCreateForm || bankTransferEditForm || currencyExchangeCreateForm || currencyExchangeEditForm) {
        const transactionTypeSelectContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const transactionTypeSelect = document.querySelector('select[id$="_transactionType"]');
        const accountCurrencyContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_currency"]');
        const accountCurrencyElement = document.querySelector('input[id$="_currency"]') as HTMLInputElement;
        const accountCurrency = accountCurrencyElement ? accountCurrencyElement.value : null;
        const transferToAccountSelect = document.querySelector('select[id$="_transferToAccount"]');
        const amountInputContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
        const newValueInputContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_newValue"]');
        const amountInput = document.querySelector('input[id$="_amount"]');
        const newValueInput = document.querySelector('input[id$="_newValue"]');
        const dateInput = document.querySelector('.input-group.date');

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
                    const selectedOption = transferToAccountSelect.options[transferToAccountSelect.selectedIndex] as HTMLOptionElement;
                    const optionText = selectedOption.textContent;
                    const selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
                    
                    const selectedAccountCurrencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency').value.trim();
                    const currencySymbolElement = newValueInputContainer.querySelector('.input-group-addon') as HTMLElement;
                    
                    currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                    (newValueInputContainer as HTMLElement).style.display = 'block';
                } else {
                    (amountInputContainer as HTMLElement).style.display = 'none';
                    (newValueInputContainer as HTMLElement).style.display = 'none';
                }
            } else {
                (newValueInputContainer as HTMLElement).style.display = 'none';

                const selectedOption = transferToAccountSelect.options[transferToAccountSelect.selectedIndex] as HTMLOptionElement;
                const optionText = selectedOption.textContent;
                const selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));

                if (selectedAccountCurrency !== accountCurrency) {
                    const selectedAccountCurrencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency').value.trim();
                    const currencySymbolElement = newValueInputContainer.querySelector('.input-group-addon') as HTMLElement;

                    currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                    (newValueInputContainer as HTMLElement).style.display = 'block';
                }
            }
        }

        $(transferToAccountSelect).on('select2:select', (e) => {
            const selectedOption = transferToAccountSelect.options[transferToAccountSelect.selectedIndex];
            const optionText = selectedOption.textContent;
            const selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
            const selectedAccountCurrencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency').value.trim();

            const currencySymbolElement = newValueInputContainer.querySelector('.input-group-addon');

            if (selectedAccountCurrency === accountCurrency) {
                newValueInputContainer.style.display = 'none';
                amountInputContainer.style.display = 'block';
            } else {
                amountInputContainer.style.display = 'block';
                currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                newValueInputContainer.style.display = 'block';
            }
        })

        /* Amount formatting */
        $(amountInput).on({
            keyup: function() {
                formatCurrency($(this));
            },
            blur: function() {
                formatCurrency($(this), "blur");
            }
        });

        /* New value formatting */
        $(newValueInput).on({
            keyup: function() {
                formatCurrency($(this));
            },
            blur: function() {
                formatCurrency($(this), "blur");
            }
        });
    }

    if (cashWithdrawalCreateForm || cashWithdrawalEditForm) {
        const transactionTypeSelectContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const accountCurrencyContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_currency"]');
        const accountCurrency = document.querySelector('input[id$="_currency"]').value;
        const transferFromAccountSelect = document.querySelector('select[id$="_transferFromAccount"]');
        const amountInputContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
        const amountFromAccountInputContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amountFromAccount"]');
        const amountInput = document.querySelector('input[id$="_amount"]');
        const amountFromAccountInput = document.querySelector('input[id$="_amountFromAccount"]');
        const dateInput = document.querySelector('.input-group.date');

        dateInput.style.width = '35%';

        if (transactionTypeSelectContainer) {
            transactionTypeSelectContainer.style.display = 'none';
        }

        if (accountCurrencyContainer) {
            accountCurrencyContainer.style.display = 'none';
        }

        if (cashWithdrawalCreateForm) {
            amountInputContainer.style.display = 'none';
            amountFromAccountInputContainer.style.display = 'none';
        }

        if (amountInput && amountFromAccountInput) {
            if (cashWithdrawalEditForm) {
                const selectedOption = transferFromAccountSelect.options[transferFromAccountSelect.selectedIndex];
                const optionText = selectedOption.textContent;
                const selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));

                if (selectedAccountCurrency !== accountCurrency) {
                    const selectedAccountCurrencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency').value.trim();
                    const currencySymbolElement = amountFromAccountInputContainer.querySelector('.input-group-addon');

                    currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                    amountFromAccountInputContainer.style.display = 'block';
                } else {
                    amountFromAccountInputContainer.style.display = 'none';
                }
            } else {
                amountInputContainer.style.display = 'none';
                amountFromAccountInputContainer.style.display = 'none';
            }
        }

        $(transferFromAccountSelect).on('select2:select', (e) => {
            const selectedOption = transferFromAccountSelect.options[transferFromAccountSelect.selectedIndex];
            const optionText = selectedOption.textContent;
            const selectedAccountCurrency = optionText.substring(optionText.indexOf("(") + 1, optionText.indexOf(")"));
            const selectedAccountCurrencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency').value.trim();

            const currencySymbolElement = amountFromAccountInputContainer.querySelector('.input-group-addon');

            if (selectedAccountCurrency === accountCurrency) {
                amountFromAccountInputContainer.style.display = 'none';
                amountInputContainer.style.display = 'block';
            } else {
                amountInputContainer.style.display = 'block';
                currencySymbolElement.textContent = selectedAccountCurrencySymbol;
                amountFromAccountInputContainer.style.display = 'block';
            }
        })

        /* Amount from formatting */
        $(amountFromAccountInput).on({
            keyup: function() {
                formatCurrency($(this));
            },
            blur: function() {
                formatCurrency($(this), "blur");
            }
        });

        /* Amount formatting */
        $(amountInput).on({
            keyup: function() {
                formatCurrency($(this));
            },
            blur: function() {
                formatCurrency($(this), "blur");
            }
        });
    }

    if (cashTransferCreateForm || cashTransferEditForm) {
        const dateInput = document.querySelector('.input-group.date');
        const amountInput = document.querySelector('input[id$="_amount"]');

        dateInput.style.width = '35%';

        /* Amount from formatting */
        $(amountInput).on({
            keyup: function() {
                formatCurrency($(this));
            },
            blur: function() {
                formatCurrency($(this), "blur");
            }
        });
    }

    if (moneyReturnCreateForm || moneyReturnEditForm) {
        const transactionTypeSelectContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const invoiceSelectContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_invoice"]');
        const transactionSelectContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transaction"]');
        const dateInput = document.querySelector('.input-group.date');

        dateInput.style.width = '35%';

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
    const addFundsCreateForm = document.querySelector('form[action*="/admin/add_funds/create"]');
    const addFundsEditForm = document.querySelector('form[action^="/admin/add_funds/"][action*="/edit"]');
    const transactionEditForm = document.querySelector('form[action^="/admin/app/transaction/"][action*="/edit"]');

    if (addFundsCreateForm || addFundsEditForm || transactionEditForm) {
        const accountSelect = document.querySelector('select[id$="_mainAccount"]');
        const transactionTypeSelectContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_transactionType"]');
        const bankFeeAmountInputContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_bankFeeAmount"]');
        const bankFeeAmountInput = document.querySelector('input[id$="_bankFeeAmount"]');
        const bankFeeNotAddedCheckbox = document.querySelector('input[id$="_bankFeeNotAdded"]');
        const amountInputContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_amount"]');
        const amountInput = document.querySelector('input[id$="_amount"]');
        const amountCurrencySymbolElement = amountInputContainer.querySelector('.input-group-addon');
        let bankFeeCurrencySymbolElement = null;
		if(bankFeeAmountInputContainer) {
			bankFeeCurrencySymbolElement = bankFeeAmountInputContainer.querySelector('.input-group-addon');
		}
        const dateInput = document.querySelector('.input-group.date');

        dateInput.style.width = '35%';

        let isBankFeeAmountContainerDisplayed;

        if (transactionTypeSelectContainer) {
            transactionTypeSelectContainer.style.display = 'none';
        }

        if (addFundsCreateForm) {
            const bankFeeCurrencySymbolElement = bankFeeAmountInputContainer.querySelector('.input-group-addon');

            if (bankFeeCurrencySymbolElement) {
                bankFeeCurrencySymbolElement.textContent = '';
            }
        }

        if (amountCurrencySymbolElement) {
            if (addFundsCreateForm) {
                amountCurrencySymbolElement.textContent = '';
            }
        }

		if(bankFeeNotAddedCheckbox) {
			if (bankFeeNotAddedCheckbox.checked == true) {
				isBankFeeAmountContainerDisplayed = false;
				bankFeeAmountInputContainer.style.display = 'none';
			} else {
				isBankFeeAmountContainerDisplayed = true;
			}
		}

        $(accountSelect).on('select2:select', (e) => {
            let selectedAccountCurrency = getAccountCurrencyCode(accountSelect);

            if (selectedAccountCurrency) {
                const currencySymbol = new Intl.NumberFormat('en', { style: 'currency', currency: selectedAccountCurrency }).formatToParts(0).find(part => part.type === 'currency').value.trim();

                bankFeeCurrencySymbolElement.textContent = currencySymbol;
                amountCurrencySymbolElement.textContent = currencySymbol;
            }
        });

		if(bankFeeNotAddedCheckbox) {
			bankFeeNotAddedCheckbox.addEventListener('change', function () {
				if (bankFeeNotAddedCheckbox.checked) {
					bankFeeAmountInputContainer.style.display = 'none';
				} else {
					bankFeeAmountInputContainer.style.display = 'block';
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