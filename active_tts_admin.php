
<?php
include_once("config.php");
include_once("adminn.php");

$re = '0000-00-00'; // Placeholder for unresolved date

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $query = "SELECT branch_name, service_number, tt, wanip, lanip, tt_reg_date, status, remark FROM `tt_registration` WHERE 1";
    $params = [];
    
    if (isset($_POST['submit_search'])) {


        $branch = mysqli_real_escape_string($link, "%{$_POST['search_term']}%");
        // Here tt_resolved_date is used with $re, which is '0000-00-00'
        $query .= " AND (branch_name LIKE ? OR service_number LIKE ? OR tt LIKE ?) AND tt_resolved_date = ?";
        array_push($params, "%$branch%", "%$branch%", "%$branch%", $re);
    }
    
    if (isset($_POST['submit_date'])) {
        $sdate = mysqli_real_escape_string($link, $_POST['sdate']);
        $edate = mysqli_real_escape_string($link, $_POST['edate']);
        $query .= " AND tt_reg_date BETWEEN ? AND ? AND tt_resolved_date = ? ";
        array_push($params, $sdate, $edate,$re);
    }
    
    $query .= " ORDER BY tt_reg_date ASC";
    $stmt = mysqli_prepare($link, $query);


    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>
 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Active TTs</title>
    <link rel="stylesheet" href="active_tts_search.css">
</head>
<body>
    <div class="container">


<div class="Search-container">
        <form method="post">
            <input type="text" name="search_term" placeholder="Branch Name, Service Number, TT" required>
            <button type="submit" name="submit_search">Search</button>
        </form>

        <form method="post" class="date-search">
            <label>From:</label> <input type="date" name="sdate" required>
            <label>To:</label> <input type="date" name="edate" required>
            <button type="submit" name="submit_date">Search</button>
        </form>
    </div>

    
    
    <?php if (isset($result) && mysqli_num_rows($result) > 0): ?>
    <div class="Table-container">
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
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['branch_name']) ?></td>
                        <td><?= htmlspecialchars($row['service_number']) ?></td>
                        <td><?= htmlspecialchars($row['tt']) ?></td>
                        <td><?= htmlspecialchars($row['wanip']) ?></td>
                        <td><?= htmlspecialchars($row['lanip']) ?></td>
                        <td><?= htmlspecialchars($row['tt_reg_date']) ?></td>
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
                        
                        <td>
                            <?php
                            $today = new DateTime();
                            $tt_date = new DateTime($row['tt_reg_date']);
                            echo $today->diff($tt_date)->days;
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
</div>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close-btn">&times;</span>
        <p id="modal-text"></p>
    </div>
</div>

        <button  onclick="exportTableToExcel('tblData')">Export to Excel</button>
    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <p>No records found.</p>
    <?php endif; ?>
</div>



    <script>
        function exportTableToExcel(tableID, filename = 'data.xls') {
    let table = document.getElementById(tableID);
    let html = table.outerHTML.replace(/ /g, '%20');
    let link = document.createElement("a");
    link.href = 'data:application/vnd.ms-excel,' + html;
    link.download = filename;
    link.click();
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
</body>
</html>
