document.addEventListener("DOMContentLoaded", function() {
    /* BUDGET ITEM CREATE & LIST */
    const budgetItemCreateForm = document.querySelector('form[action^="/admin/app/budgetitem/create"]');
    const budgetItemEditForm = document.querySelector('form[action^="/admin/app/budgetitem/"][action*="/edit"]');

    const budgetItemListForm = document.querySelector('form[action="/admin/app/budgetitem/list"]');
    let budgetMainCategorySelect: HTMLSelectElement | null = document.querySelector('select[id*="_budgetMainCategory"]');
    let budgetSubCategorySelect: HTMLSelectElement | null = document.querySelector('select[id*="_budgetSubCategory"]');

    if (budgetItemCreateForm || budgetItemEditForm || budgetItemListForm) {
        if (budgetItemCreateForm || budgetItemEditForm) {

            budgetMainCategorySelect = budgetItemCreateForm 
                ? budgetItemCreateForm.querySelector('select[id*="_budgetMainCategory"]') 
                    : budgetItemEditForm 
                ? budgetItemEditForm.querySelector('select[id*="_budgetMainCategory"]') 
                    : null;
                    budgetSubCategorySelect = budgetItemCreateForm 
                ? budgetItemCreateForm.querySelector('select[id*="_budgetSubCategory"]') 
                    : budgetItemEditForm 
                ? budgetItemEditForm.querySelector('select[id*="_budgetSubCategory"]') 
                    : null;

            const currencySelect = document.querySelector('select[id$="_currency"]');
            const budgetInputContainer = document.querySelector('div[id^="sonata-ba-field-container-"][id$="_budgeted"]');
            let currencySymbolElement: HTMLElement | null = null;
            let budgetedInput: HTMLInputElement | null = null;

            if (budgetInputContainer) {
                currencySymbolElement = budgetInputContainer.querySelector('.input-group-addon');
                budgetedInput = budgetInputContainer.querySelector('input[id$="_budgeted"]');
            }

            /* CURRENCY SELECT*/
            if (currencySelect) {
                $(currencySelect).on('select2:select', (e: JQuery.Event) => {
                    const selectedCurrency = (currencySelect as HTMLSelectElement).value;
                    const currencyPart = new Intl.NumberFormat('en', { style: 'currency', currency: selectedCurrency })
                        .formatToParts(0).find(part => part.type === 'currency');

                    const currencySymbol = currencyPart ? currencyPart.value.trim() : '';

                    if (currencySymbolElement) {
                        currencySymbolElement.textContent = currencySymbol;
                    }
                });
            }

            if (budgetedInput) {
                $(budgetedInput).on({
                    keyup: function() {
                        formatCurrency($(this), '');
                    },
                    blur: function() {
                        formatCurrency($(this), 'blur');
                    }
                });
            }
        } else if (budgetItemListForm) {
            budgetMainCategorySelect = budgetItemListForm.querySelector('select[id="filter_budgetMainCategory_value"]');
            budgetSubCategorySelect = budgetItemListForm.querySelector('select[id="filter_budgetSubCategory_value"]');
        }

        /* BUDGET CATEGORIES SELECT */
        if (budgetMainCategorySelect) {
            if (budgetMainCategorySelect.selectedIndex === 0) {
                if (budgetSubCategorySelect) {
                    budgetSubCategorySelect.innerHTML = '';
                }
            } else {
                const budgetMainCategoryId = budgetMainCategorySelect.value;

                fetchBudgetCategories(budgetMainCategoryId);
            }
        }

        if (budgetMainCategorySelect) {
            $(budgetMainCategorySelect).on('select2:select', (e: JQuery.Event) => {
                if (budgetMainCategorySelect) {
                    const budgetMainCategoryId = budgetMainCategorySelect.value;
                    fetchBudgetCategories(budgetMainCategoryId, 'mainCategorySelect');
                }
            });
        }

        if (budgetSubCategorySelect) {
            $(budgetSubCategorySelect).on('select2:select', (e: JQuery.Event) => {
                if (budgetSubCategorySelect) {
                    const selectedbudgetSubCategoryId = budgetSubCategorySelect.value;
                    localStorage.setItem('selectedbudgetSubCategoryId', selectedbudgetSubCategoryId);
                }
            });
        }

        function fetchBudgetCategories(budgetMainCategoryId: string, flag: string | null = null) {
            fetch('/get_budget_categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ mainCategoryId: budgetMainCategoryId }),
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Handle response from the server

                // Proceed with the standard form submission
                if (budgetSubCategorySelect !== null) {
                    updatebudgetSubCategoryList(budgetSubCategorySelect, data, flag);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function updatebudgetSubCategoryList(
            budgetSubCategorySelect: HTMLSelectElement,
            filteredBudgetCategories: any[], 
            flag: string | null
        ) {
            let selectedbudgetSubCategoryId: string | null = null;

            if (budgetItemEditForm) {
                if (flag == null) {
                    const selectedOption = budgetSubCategorySelect.options[budgetSubCategorySelect.selectedIndex];
                    selectedbudgetSubCategoryId = selectedOption.getAttribute('value');
                }
            } else {
                selectedbudgetSubCategoryId = localStorage.getItem('selectedbudgetSubCategoryId');
            }

            budgetSubCategorySelect.innerHTML = '';

            if (filteredBudgetCategories) {
                const newbudgetSubCategorySelect = document.createElement('select');
                newbudgetSubCategorySelect.id = budgetSubCategorySelect.id;
                newbudgetSubCategorySelect.name = budgetSubCategorySelect.name;
                newbudgetSubCategorySelect.className = budgetSubCategorySelect.className;
                newbudgetSubCategorySelect.setAttribute('data-placeholder', 'Choose a Sub-Category');

                for (const category of filteredBudgetCategories) {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.text = category.name;
                    option.selected = category.selected;

                    budgetSubCategorySelect.appendChild(option);
                }

                $(budgetSubCategorySelect).trigger('change');

                if (selectedbudgetSubCategoryId !== null) {
                    $(budgetSubCategorySelect).val(selectedbudgetSubCategoryId).trigger('change');

                    localStorage.removeItem('selectedbudgetSubCategoryId');
                }
            }
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