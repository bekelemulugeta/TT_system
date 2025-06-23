<?php   
require_once("adminn.php");

// Set default return date
$Ld = '0000-00-00';

// Use prepared statements for SQL query to avoid SQL injection
$queryyy = "SELECT id, imei, branch, date_taken, taken_by, return_date, return_by FROM `4g_loan` WHERE return_date != ? ORDER BY return_date DESC";
$stmt = mysqli_prepare($link, $queryyy);
mysqli_stmt_bind_param($stmt, 's', $Ld);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?> 

<html>
<head>
    <title>4G Loan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link href="4g_report_admin.css" rel="stylesheet" type="text/css"> <!-- External CSS -->
    <script type="text/javascript" src="jquery.inputmask.bundle.js"></script>
</head>
<body>

    <div class="container">
    

    <h2>4G Loan Report</h2>
        <table id="tblData" class="table table-bordered">
            <thead>
                <tr>
                    <th>IMEI</th>
                    <th>Branch</th>   
                    <th>Date Taken</th>  
                    <th>Taken By</th> 
                    <th>Return Date</th>  
                    <th>Return By</th>   
                </tr>  
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_array($result)) { ?>
                    <tr id="row-<?php echo $row['id']; ?>">
                        <td><?php echo htmlspecialchars($row['imei']); ?></td>
                        <td><?php echo htmlspecialchars($row['branch']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_taken']); ?></td>
                        <td><?php echo htmlspecialchars($row['taken_by']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_by']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

         <div class="export-container">
            <button onclick="exportTableToExcel('tblData')">Export</button>
<a href="4g_loan_admin.php" class="w3-bar-item w3-button">Go To Loan Page</a>
        </div> 

        
    </div>

</body>

<script type="text/javascript">
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

        // Specify file name
        filename = filename ? filename + '.xls' : 'excel_data.xls';

        // Create download link element
        downloadLink = document.createElement("a");

        document.body.appendChild(downloadLink);

        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], { type: dataType });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            // Create a link to the file
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

            // Setting the file name
            downloadLink.download = filename;

            // Triggering the function
            downloadLink.click();
        }
    }
</script>

</html>
