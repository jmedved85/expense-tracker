document.addEventListener("DOMContentLoaded", function() {
    /* PURCHASE FORM */
    const purchaseCreateForm = document.querySelector<HTMLFormElement>('form[action^="/admin/app/purchase/create"]');
    const purchaseEditForm = document.querySelector<HTMLFormElement>('form[action^="/admin/app/purchase"][action*="/edit"]');
    const amountTotalPurchaseContainer = document.querySelector<HTMLDivElement>('div[id^="sonata-ba-field-container-"][id$="_amount"]');
    const purchaseLinesContainer = document.querySelector<HTMLDivElement>('div[id^="sonata-ba-field-container-"][id$="_purchaseLines"]');
    
    if (purchaseCreateForm || purchaseEditForm) {
        const purchaseDateInput = document.querySelectorAll<HTMLDivElement>('.input-group.date');
        const realAmountPaidContainer = document.querySelector<HTMLDivElement>('div[id^="sonata-ba-field-container-"][id$="_realAmountPaid"]');
        let realAmountPaidInput: HTMLInputElement | null = null;
    
        /* Currency symbol on Real Amount Paid input */
        if (realAmountPaidContainer) {
            const currencySymbolElementRealAmountPaid = realAmountPaidContainer.querySelector<HTMLSpanElement>('.input-group-addon');
            const realCurrencySelectField = document.querySelector<HTMLSelectElement>('select[id$="_realCurrency"]');
            realAmountPaidInput = realAmountPaidContainer.querySelector<HTMLInputElement>('input[id$="_realAmountPaid"]');
    
            if (realCurrencySelectField && realCurrencySelectField.value === '') {
                if (currencySymbolElementRealAmountPaid) {
                    currencySymbolElementRealAmountPaid.innerHTML = '';
                }
            } else if (realCurrencySelectField) {
                const currencySymbol = 
                    new Intl.NumberFormat('en', { style: 'currency', currency: realCurrencySelectField.value })
                    .formatToParts(0).find(part => part.type === 'currency')?.value.trim();
    
                if (currencySymbol && currencySymbolElementRealAmountPaid) {
                    currencySymbolElementRealAmountPaid.innerHTML = currencySymbol;
                }
            }

            if (realCurrencySelectField) {
                $(realCurrencySelectField).on('select2:select', (e) => {
                    const currencySymbol = 
                        new Intl.NumberFormat('en', { style: 'currency', currency: realCurrencySelectField?.value })
                        .formatToParts(0).find(part => part.type === 'currency')?.value.trim();
                    
                    if (currencySymbol && currencySymbolElementRealAmountPaid) {
                        currencySymbolElementRealAmountPaid.innerHTML = currencySymbol;
                    }
                });

                $(realCurrencySelectField).on('select2:clear', (e) => {
                    if (currencySymbolElementRealAmountPaid) {
                        currencySymbolElementRealAmountPaid.innerHTML = '';
                    }
                    if (realAmountPaidInput) {
                        realAmountPaidInput.value = '';
                    }
                });
            }
        }
    
        purchaseDateInput.forEach(item => {
            item.style.width = '35%';
        });

        if (amountTotalPurchaseContainer && purchaseLinesContainer) {
            /* Amount (Purchase Lines Total) calculation && Budget Category filtration */
            let purchaseAmountTotal = amountTotalPurchaseContainer.querySelector('input[id$="_amount"]');
            let selectedBudgetCategories: { key: string, value: { id: string, name: string, selected: boolean }[] }[] = [];

            purchaseLinesContainer.addEventListener('click', (e: MouseEvent) => {
                const target = e.target as HTMLElement;
                if (target.matches('a[title="Add new"]')) {
                    /* PURCHASE TOTAL SUM CALCULATION */
                    let lineTotalsSum = 0;

                    const lineTotals = purchaseLinesContainer.querySelectorAll('input[id$="_lineTotal"]');
                    
                    lineTotals.forEach((lineTotal) => {
                        let lineTotalValue = (lineTotal as HTMLInputElement).value.replace(/,/g, '');
                        let lineTotalValueFloat = parseFloat(lineTotalValue);
                    
                        if (!isNaN(lineTotalValueFloat)) {
                            lineTotalsSum += lineTotalValueFloat;
                        }
                    });

                    const formattedSum = lineTotalsSum.toFixed(2);

                    if (purchaseAmountTotal) {
                        (purchaseAmountTotal as HTMLInputElement).value = formattedSum;
                    }

                    const purchaseLineItems = purchaseLinesContainer.querySelectorAll('.ui-sortable-handle');

                    purchaseLineItems.forEach((item) => {
                        const budgetMainCategorySelect = (item as HTMLElement).querySelector('select[id*="_budgetMainCategory"]');
                        const budgetCategorySelect = (item as HTMLElement).querySelector('select[id*="_budgetCategory"]');
                    
                        if (budgetMainCategorySelect 
                            && (budgetMainCategorySelect as HTMLSelectElement).selectedIndex > 0 
                            && budgetCategorySelect
                        ) {
                            const categoryElementId = (budgetCategorySelect as HTMLSelectElement).id;
                            const categoryOptions = (budgetCategorySelect as HTMLSelectElement).querySelectorAll('option');
                            const optionsArr: { id: string, name: string, selected: boolean }[] = [];
                        
                            categoryOptions.forEach((option: HTMLOptionElement) => {
                                const optionIdText = {
                                    id: option.value,
                                    name: option.text,
                                    selected: option.selected
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
                    function checkForPurchaseLineRowToOpen() {
                        if (purchaseLinesContainer) {
                            const purchaseLinesRows = purchaseLinesContainer.querySelector('.sonata-ba-tbody.ui-sortable')
                            ?? purchaseLinesContainer.querySelector('.sonata-ba-collapsed-fields');

                            if (purchaseLinesRows) {
                                const purchaseLineTableStyleItems = purchaseLinesContainer.querySelectorAll('.ui-sortable-handle');
                                const purchaseLineFormStyleItems = purchaseLinesContainer.querySelectorAll('fieldset');

                                const purchaseLineItems = 
                                    purchaseLineTableStyleItems.length > 0 ? purchaseLineTableStyleItems : purchaseLineFormStyleItems;

                                purchaseLineItems.forEach((item) => {
                                    const budgetMainCategorySelect = (item as HTMLElement).querySelector('select[id*="_budgetMainCategory"]');
                                    const budgetCategorySelect = (item as HTMLElement).querySelector('select[id*="_budgetCategory"]');
                                    const netValueInput = (item as HTMLElement).querySelector('input[id*="_netValue"]');
                                    const vatInput = (item as HTMLElement).querySelector('input[id*="_vat"]');
                                    const vatValueInput = (item as HTMLElement).querySelector('input[id*="_vatValue"]');
                                    const lineTotalInput = (item as HTMLElement).querySelector('input[id*="_lineTotal"]');
                                
                                    if (Array.isArray(selectedBudgetCategories) && budgetMainCategorySelect && budgetCategorySelect) {
                                        if (selectedBudgetCategories.length > 0) {
                                            selectedBudgetCategories.forEach((selectedCategory) => {
                                                if ((budgetMainCategorySelect as HTMLSelectElement).selectedIndex > 0) {
                                                    if ((budgetCategorySelect as HTMLSelectElement).id == selectedCategory.key) {
                                                        updateBudgetCategoryList(
                                                            budgetCategorySelect as HTMLSelectElement, 
                                                            selectedCategory.value
                                                        );
                                                    }
                                                }
                                            });
                                        }
                                    }

                                    if (budgetMainCategorySelect 
                                        && (budgetMainCategorySelect as HTMLSelectElement).selectedIndex === 0 
                                        && budgetCategorySelect
                                    ) {
                                        (budgetCategorySelect as HTMLSelectElement).innerHTML = '';
                                    }

                                    if (budgetMainCategorySelect) {
                                        $(budgetMainCategorySelect).on('select2:select', (e) => {
                                        // $(budgetMainCategorySelect).on('select2:select', (e: JQuery.Select2.SelectEvent) => {
                                            const selectedOption = (budgetMainCategorySelect as HTMLSelectElement).options[
                                                (budgetMainCategorySelect as HTMLSelectElement).selectedIndex];
                                            let mainCategoryId = selectedOption.value;

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
                                                if (budgetCategorySelect) {
                                                    updateBudgetCategoryList(budgetCategorySelect as HTMLSelectElement, data);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                            });
                                        });
                                    }

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
                                });

                                clearInterval(interval);
                            }
                        }
                    }

                    const interval = setInterval(checkForPurchaseLineRowToOpen, 1000);
                }
            });
        }

        if (purchaseEditForm && purchaseLinesContainer) {
            const purchaseLinesRows = purchaseLinesContainer.querySelector('.sonata-ba-tbody.ui-sortable')
                ?? purchaseLinesContainer.querySelector('.sonata-ba-collapsed-fields');

            if (purchaseLinesRows) {
                const purchaseLineTableStyleItems = purchaseLinesContainer.querySelectorAll('.ui-sortable-handle');
                const purchaseLineFormStyleItems = purchaseLinesContainer.querySelectorAll('fieldset');

                const purchaseLineItems = purchaseLineTableStyleItems.length > 0 ? purchaseLineTableStyleItems : purchaseLineFormStyleItems;

                purchaseLineItems.forEach((item: Element) => {
                    const fieldsetItem = item as HTMLFieldSetElement;
                    const budgetMainCategorySelect = fieldsetItem.querySelector('select[id*="_budgetMainCategory"]') as HTMLSelectElement;
                    const budgetCategorySelect = fieldsetItem.querySelector('select[id*="_budgetCategory"]') as HTMLSelectElement;
                    const netValueInput = fieldsetItem.querySelector('input[id*="_netValue"]') as HTMLInputElement;
                    const vatInput = fieldsetItem.querySelector('input[id*="_vat"]') as HTMLInputElement;
                    const vatValueInput = fieldsetItem.querySelector('input[id*="_vatValue"]') as HTMLInputElement;
                    const lineTotalInput = fieldsetItem.querySelector('input[id*="_lineTotal"]') as HTMLInputElement;
                
                
                    if (budgetMainCategorySelect) {
                        if (budgetMainCategorySelect.selectedIndex === 0) {
                            budgetCategorySelect.innerHTML = '';
                        }
                
                        $(budgetMainCategorySelect).on('select2:select', (e) => {
                        // $(budgetMainCategorySelect).on('select2:select', (e: JQuery.Select2.SelectEvent) => {
                            const selectedOption = budgetMainCategorySelect.options[budgetMainCategorySelect.selectedIndex];
                            let mainCategoryId = selectedOption.value;
                
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
                    }

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
                });
            }
        }

        interface Category {
            id: string;
            name: string;
            selected: boolean;
        }
        
        function updateBudgetCategoryList(
            budgetCategorySelect: HTMLSelectElement, 
            filteredBudgetCategories: Category[] | null
        ): void {
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

        if (realAmountPaidInput) {
            $(realAmountPaidInput).on({
                keyup: function() {
                    formatCurrency($(this), '');
                },
                blur: function() {
                    formatCurrency($(this), "blur");
                }
            });
        }
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