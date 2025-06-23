// Delete row functionality
document.querySelectorAll('.remove').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        const rowId = row.id;

        // Send a request to delete the row data from the database (via PHP)
        fetch('facility_del.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + rowId
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                row.remove(); // Remove the row from the table
            } else {
                alert('Error deleting row.');
            }
        });
    });
});

// Export table to Excel
function exportTableToExcel(tableID) {
    const table = document.getElementById(tableID);
    let html = table.outerHTML;

    const uri = 'data:application/vnd.ms-excel;base64,';
    const base64 = function (s) {
        return window.btoa(unescape(encodeURIComponent(s)));
    };
    const format = function (s) {
        return s.replace(/[\r\n]/g, '').replace(/\s+/g, ' ').replace(/<br>/g, '\n');
    };

    const link = document.createElement('a');
    link.href = uri + base64(format(html));
    link.download = 'table_data.xls';
    link.click();
}
