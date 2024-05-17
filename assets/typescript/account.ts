/* Sorting transaction tables in Account show on Date DESC and TransactionNumber DESC */
const tableTransaction = document.querySelector("#tableTransaction tbody");

if (tableTransaction) {
    // Get all <tr>'s as array
    let rows = [].slice.call(tableTransaction.querySelectorAll("tr"));
    
    rows.sort(function (a, b) {
        return (
            convertDateTimeToTimestamp(b.cells[0].innerText, b.cells[1].innerText) - convertDateTimeToTimestamp(a.cells[0].innerText, a.cells[1].innerText)
        );
    });

    rows.forEach(function(element) {
        // Moving element
        tableTransaction.appendChild(element);
    });

    function convertDateTimeToTimestamp(dateInnerText, transactionNumberInnerText) {
        const transactionNumber = parseInt(transactionNumberInnerText);

        const [dateComponents] = dateInnerText.split(' ');
        const [day, month, year] = dateComponents.split('/');
        const date = new Date(+year, month - 1, +day);
        const timestamp = date.getTime();

        return timestamp + transactionNumber;
    }
}