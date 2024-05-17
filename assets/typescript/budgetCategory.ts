document.addEventListener("DOMContentLoaded", function() {
     /* BUDGET CATEGORY LIST */
     const budgetMainCategoryList = document.querySelector('#budget-main-category-list');

     if (budgetMainCategoryList) {
        const budgetMainCategoryTableBody = budgetMainCategoryList.querySelector('#budgetMainCategories');
        const budgetSubCategoryList = document.querySelector('#budget-sub-category-list');

        if (budgetMainCategoryTableBody) {
            const budgetMainCategoryTableTextCells = 
                budgetMainCategoryTableBody.querySelectorAll('.sonata-ba-list-field.sonata-ba-list-field-string');
            const budgetMainCategoryTableActionCells = 
                budgetMainCategoryTableBody.querySelectorAll('.sonata-ba-list-field.sonata-ba-list-field-actions');
            const subCategoriesButtons = budgetMainCategoryList.querySelectorAll('#sub-categories-button');

            budgetMainCategoryTableTextCells.forEach((cell: Element) => {
                (cell as HTMLElement).style.lineHeight = "2";
            });
            
            budgetMainCategoryTableActionCells.forEach((cell: Element) => {
                (cell as HTMLElement).style.textAlign = "center";
            });

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
                        console.log(data); // Handle response from the server

                        // Proceed with the standard form submission
                        updatebudgetSubCategoryList(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });

            function updatebudgetSubCategoryList(data) {
                if (budgetSubCategoryList) {
                    const budgetSubCategoriesTableBody = budgetSubCategoryList.querySelector('#budgetSubCategories');

                    if (budgetSubCategoriesTableBody) {
                        budgetSubCategoriesTableBody.innerHTML = '';
        
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
            
                                const groupListDiv = document.createElement('div');
                                groupListDiv.classList.add('btn-group-list');
                
                                // Edit button
                                const editButton = document.createElement('a');
                                editButton.href = `/admin/app/budgetsubcategory/${item.id}/edit`;
                                editButton.classList.add('btn', 'btn-xs', 'btn-default', 'edit_link');
                                editButton.title = 'Edit';
                                
                                const editButtonIcon = document.createElement('i');
                                editButtonIcon.classList.add('fas', 'fa-pencil-alt');
                                editButtonIcon.setAttribute('aria-hidden', 'true');
                                
                                editButton.appendChild(editButtonIcon);
                                
                                // const editText = document.createTextNode(' Edit');
                                // editButton.appendChild(editText);
                
                                // Delete button
                                const deleteButton = document.createElement('a');
                                deleteButton.href = `/admin/app/budgetsubcategory/${item.id}/delete`;
                                deleteButton.classList.add('btn', 'btn-xs', 'btn-danger', 'delete_link');
                                deleteButton.style.marginLeft = '5px';
                                deleteButton.title = 'Delete';
                
                                const deleteButtonIcon = document.createElement('i');
                                deleteButtonIcon.classList.add('fas', 'fa-times');
                                deleteButtonIcon.setAttribute('aria-hidden', 'true');
                                
                                deleteButton.appendChild(deleteButtonIcon);
                
                                // const deleteText = document.createTextNode(' Delete');
                                // deleteButton.appendChild(deleteText);
            
                                groupListDiv.appendChild(editButton);
                                groupListDiv.appendChild(deleteButton);
                
                                // Append to Actions cell
                                // actionsCell.appendChild(editButton);
                                // actionsCell.appendChild(deleteButton);
            
                                actionsCell.appendChild(groupListDiv);
            
                                // Append cells to row
                                newRow.appendChild(nameCell);
                                newRow.appendChild(actionsCell);
                
                                budgetSubCategoriesTableBody.appendChild(newRow);
                            });
                        } else {
                            // Create a new row
                            const newRow = document.createElement('tr');
                            const newCell = document.createElement('td');
                            const newSpan = document.createElement('span');
                            newCell.colSpan = 2;
                            newCell.classList.add('sonata-ba-list-field', 'sonata-ba-list-field-string');
                            newSpan.id = 'budget-category-list-no-results';
                            newSpan.classList.add('budget-category-list-no-results');
                            newSpan.textContent = 'No Results - Please add a Sub Category of the Main Category.';
            
                            // Append cells to row
                            newRow.appendChild(newCell);
                            newCell.appendChild(newSpan);
            
                            budgetSubCategoriesTableBody.appendChild(newRow);
                        }
                    }
                }
            }
        }
    }
});