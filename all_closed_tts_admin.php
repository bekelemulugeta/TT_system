<?php

include_once("adminn.php");

// Initialize variables
$re = '0000-00-00';
$query = "SELECT branch_name, service_number, tt, wanip, lanip, tt_reg_date,tt_resolved_date, status, remark FROM `tt_registration` WHERE tt_resolved_date != '$re' ORDER BY tt_reg_date ASC";
$result = mysqli_query($link, $query);

// Check if there are results
if (mysqli_num_rows($result) > 0) :
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Closed TTs</title>
    <!-- Link to the external CSS file -->
    <link href="all_closed_tts_admin.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">
<h2>All Closed TTs</h2>
    <table id="tblData" class="table table-bordered">
        <thead>
            <tr>
                <th>Branch Name</th>
                <th>Service Number</th>
                <th>TT</th>
                <th>WAN IP</th>
                <th>LAN IP</th>
                <th>Reg. Date</th>
                <th>Closed. Date</th>
                <th>Status</th>
                <th>Remark</th>
                
            </tr>
        </thead>
        <tbody>
    <?php while ($row = mysqli_fetch_array($result)) : ?>

        <tr>
            <td data-label="Branch Name"><?php echo $row['branch_name']; ?></td>
            <td data-label="Service Number"><?php echo $row['service_number']; ?></td>
            <td data-label="TT"><?php echo $row['tt']; ?></td>
            <td data-label="WAN IP"><?php echo $row['wanip']; ?></td>
            <td data-label="LAN IP"><?php echo $row['lanip']; ?></td>
            <td data-label="TT Reg. Date"><?php echo $row['tt_reg_date']; ?></td>
            <td data-label="TT Reg. Date"><?php echo $row['tt_resolved_date']; ?></td>
            <td data-label="Status">
    <span class="clickable" onclick="openModal('<?php echo addslashes($row['status']); ?>')">
        <?php echo substr($row['status'], 0, 20) . (strlen($row['status']) > 20 ? "..." : ""); ?>
    </span>
</td>
<td data-label="Remark">
    <span class="clickable" onclick="openModal('<?php echo addslashes($row['remark']); ?>')">
        <?php echo substr($row['remark'], 0, 20) . (strlen($row['remark']) > 20 ? "..." : ""); ?>
    </span>
</td>

        </tr>
    <?php endwhile; ?>
</tbody>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close-btn">&times;</span>
        <p id="modal-text"></p>
    </div>
</div>

    </table>

    <div class="export-btn">
        <button onclick="exportTableToExcel('tblData')">Export Table Data to Excel</button>
    </div>


</div>


<?php else : ?>
    <p>No Active TT found</p>
<?php endif; ?>

<?php mysqli_close($link); ?>

</body>
</html>
<script>
// Function to export table data to Excel
function exportTableToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    filename = 'All closed TTs.xls'; // Default file name
    
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);

    if (navigator.msSaveOrOpenBlob) {
        var blob = new Blob(['\ufeff', tableHTML], { type: dataType });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}

// Function to open modal and display full text
function openModal(text) {
    document.getElementById("modal-text").innerText = text;
    document.getElementById("myModal").style.display = "block";
}

// Close modal when clicking the close button
document.getElementById("close-modal").addEventListener("click", function () {
    document.getElementById("myModal").style.display = "none";
});

// Close modal when clicking outside the content
window.onclick = function (event) {
    if (event.target == document.getElementById("myModal")) {
        document.getElementById("myModal").style.display = "none";
    }
};

</script>
