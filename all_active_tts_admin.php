
<?php
require_once("adminn.php");

$re = '0000-00-00';

// Secure Query Using Prepared Statements
$query = "SELECT branch_name, service_number, tt, wanip, lanip, tt_reg_date, status, remark 
          FROM `tt_registration` WHERE tt_resolved_date = ? ORDER BY tt_reg_date ASC";

$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $re);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active TTs</title>
    <link href="all_active_tts_admin.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">
    <div class="table">
    <h2>Active TTs</h2>

    <?php if (mysqli_num_rows($result) > 0) : ?>
        <table id="tblData" class="table table-bordered">
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>Service Number</th>
                    <th>TT</th>
                    <th>WAN IP</th>
                    <th>LAN IP</th>
                    <th>TT Reg. Date</th>
                    <th>Status</th>
                    <th>Remark</th>
                    <th>Days</th>
                </tr>
            </thead>
            <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <?php
            $today = date("Y-m-d");
            $day = $row['tt_reg_date'] ?? '';
            $days = ($day && strtotime($day)) ? (strtotime($today) - strtotime($day)) / (60 * 60 * 24) : 'N/A';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['branch_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['service_number'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['tt'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['wanip'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['lanip'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['tt_reg_date'], ENT_QUOTES, 'UTF-8'); ?></td>
               


 <td>
                                    <span class="clickable" onclick="openModal('<?php echo addslashes($row['status']); ?>')">
                                        <?php echo substr($row['status'], 0, 20) . (strlen($row['status']) > 20 ? "..." : ""); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="clickable" onclick="openModal('<?php echo addslashes($row['remark']); ?>')">
                                        <?php echo substr($row['remark'], 0, 20) . (strlen($row['remark']) > 20 ? "..." : ""); ?>
                                    </span>
                                </td>


                <td data-label="Days"><?php echo $days; ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
         <div class="export-btn">
            <button onclick="exportTableToExcel('tblData')">Export Table Data to Excel</button>
        </div>

        </table>
  </div>
        <div class="update">
            
      

        <?php include("status_remark_update_admin.php"); ?>
  </div>

    <?php else : ?>
        <p>No Active TT found</p>
    <?php endif; ?>

   
</div>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close-btn">&times;</span>
        <p id="modal-text"></p>
    </div>
</div>


</body>
</html>

<script>
// Function to export table data to Excel
function exportTableToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    filename = 'All Active TTs.xls'; // Default file name
    
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
