<?php

require_once("adminn.php");

// Use prepared statements to prevent SQL injection
$query = "SELECT id, Brand_Name, Serial_Number, Model, Year_Installed, Operating_System, Haypervisor_Name, 
                 Location, Memory, Disc, Processor, Capacity, Virtual_Server, Value 
          FROM inventory ORDER BY Location ASC";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total count securely
$query_count = "SELECT COUNT(*) as total FROM inventory";
$count_result = mysqli_query($link, $query_count);
$count_row = mysqli_fetch_assoc($count_result);
$total = $count_row['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link href="inventory.css" rel="stylesheet">
</head>
<body>
      <div class="container">
<h2>Inventory</h2>
<table id="tblData" class="table table-bordered">
    <thead>
        <tr>
            <th>Brand Name/Type</th>
            <th>Serial Number</th>   
            <th>Model</th>  
            <th>Year Installed</th>  
            <th>Operating System</th>
            <th>Hypervisor Name</th>  
            <th>Location</th> 
            <th>Memory</th>
            <th>Disc</th>  
            <th>Processor</th>
            <th>Capacity</th>
            <th>No Of Virtual</th>
            <th>Asset Value</th>
        </tr>  
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>   
        <tr id="<?php echo htmlspecialchars($row['id']); ?>">
            <td><?php echo htmlspecialchars($row['Brand_Name']); ?></td> 
            <td><?php echo htmlspecialchars($row['Serial_Number']); ?></td> 
            <td><?php echo htmlspecialchars($row['Model']); ?></td>  
            <td><?php echo htmlspecialchars($row['Year_Installed']); ?></td>  
            <td><?php echo htmlspecialchars($row['Operating_System']); ?></td>
            <td><?php echo htmlspecialchars($row['Haypervisor_Name']); ?></td>
            <td><?php echo htmlspecialchars($row['Location']); ?></td>
            <td><?php echo htmlspecialchars($row['Memory']); ?></td>
            <td><?php echo htmlspecialchars($row['Disc']); ?></td>
            <td><?php echo htmlspecialchars($row['Processor']); ?></td>
            <td><?php echo htmlspecialchars($row['Capacity']); ?></td>
            <td><?php echo htmlspecialchars($row['Virtual_Server']); ?></td>
            <td><?php echo htmlspecialchars($row['Value']); ?></td>
        </tr>  
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <td colspan="12"><?php echo htmlspecialchars($total); ?></td>  
        </tr>
    </tfoot>
</table>



<div class="export-container">
    <button onclick="exportTableToExcel('tblData')">Export to Excel</button>
</div>
</div> 
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".remove").forEach(button => {
        button.addEventListener("click", function() {
            var row = this.closest("tr");
            var id = row.getAttribute("id");

            if (confirm('Are you sure you want to delete this record?')) {
                fetch(`inventory_del.php?id=${encodeURIComponent(id)}`)
                .then(response => response.text())
                .then(data => {
                    row.remove();
                    alert("Information deleted successfully");
                })
                .catch(() => alert('Something went wrong'));
            }
        });
    });
});

function exportTableToExcel(tableID, filename = 'inventory_data.xls') {
    let table = document.getElementById(tableID);
    let tableHTML = table.outerHTML.replace(/ /g, '%20');
    let downloadLink = document.createElement("a");
    
    downloadLink.href = 'data:application/vnd.ms-excel, ' + tableHTML;
    downloadLink.download = filename;
    downloadLink.click();
}
</script>

</body>
</html>
