<?php
include_once("config.php");
include_once("adminn.php");

// Get branch list
$dept_query = "SELECT DISTINCT branch_name FROM service_info ORDER BY branch_name ASC";
$branch_result = mysqli_query($link, $dept_query);

// Initialize variables to prevent warnings
$all = $resolved = $unresolved = 0;
$result = null;
$start = $end = $selectedBranch = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_search'])) {
    $re = '0000-00-00';
    
    $start = !empty($_POST['sdate']) ? mysqli_real_escape_string($link, $_POST['sdate']) : '';
    $end = !empty($_POST['edate']) ? mysqli_real_escape_string($link, $_POST['edate']) : '';

    // Ensure date range is valid
    if (empty($start) || empty($end)) {
        die("Error: Please select valid dates.");
    }

    // Branch filter handling
    $branchFilter = "";
    if (!empty($_POST['branch'])) {
        $selectedBranch = mysqli_real_escape_string($link, $_POST['branch']);
        $branchFilter = " AND branch_name = '$selectedBranch'";
    }

    // Main data query
    $queryyy = "SELECT branch_name, service_number, tt, tt_reg_date, tt_resolved_date, status, remark 
                FROM `tt_registration` 
                WHERE tt_reg_date BETWEEN '$start' AND '$end' $branchFilter 
                ORDER BY tt_reg_date ASC";

    // Count queries
    $quer = "SELECT COUNT(*) as alll FROM `tt_registration` WHERE tt_reg_date BETWEEN '$start' AND '$end' $branchFilter";
    $qu = "SELECT COUNT(*) as solved FROM `tt_registration` WHERE tt_reg_date BETWEEN '$start' AND '$end' AND tt_resolved_date != '$re' $branchFilter";
    $q = "SELECT COUNT(*) as unresolved FROM `tt_registration` WHERE tt_reg_date BETWEEN '$start' AND '$end' AND tt_resolved_date = '$re' $branchFilter";

    // Execute queries
    $resultt = mysqli_query($link, $quer);
    $all = mysqli_fetch_assoc($resultt)['alll'] ?? 0;

    $resul = mysqli_query($link, $qu);
    $resolved = mysqli_fetch_assoc($resul)['solved'] ?? 0;

    $resu = mysqli_query($link, $q);
    $unresolved = mysqli_fetch_assoc($resu)['unresolved'] ?? 0;

    // Fetch main result set
    $result = mysqli_query($link, $queryyy);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Report</title>
    <link rel="stylesheet" href="all_report.css">
</head>
<body>
    <div class="container">
        <h2>Generate TT Report</h2>

        <div class="Search-container">
            <form method="post">
                <label>Branch:</label>
                <select name="branch" id="branch">
                    <option value="" selected>All Branches</option>
                    <?php while ($row = mysqli_fetch_assoc($branch_result)) { ?>
                        <option value="<?php echo htmlspecialchars($row['branch_name']); ?>"
                            <?php echo ($row['branch_name'] === $selectedBranch) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($row['branch_name']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>From:</label> 
                <input type="date" name="sdate" value="<?php echo htmlspecialchars($start); ?>" required>
                
                <label>To:</label> 
                <input type="date" name="edate" value="<?php echo htmlspecialchars($end); ?>" required>
                
                <button type="submit" name="submit_search">Search</button>
            </form>
        </div>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="Table-container">
                <table id="tblData" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Branch Name</th>
                            <th>Service Number</th>
                            <th>TT</th>
                            <th>Registration Date</th>
                            <th>Resolved Date</th>
                            <th>Status</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['service_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['tt']); ?></td>
                                <td><?php echo htmlspecialchars($row['tt_reg_date']); ?></td>
                                <td style="color: <?php echo ($row['tt_resolved_date'] == '0000-00-00') ? 'green' : 'black'; ?>;">
    <?php echo ($row['tt_resolved_date'] == '0000-00-00') ? "Active" : htmlspecialchars($row['tt_resolved_date']); ?>
</td>
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
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"><strong>Total Cases:</strong> <?php echo $all; ?></td>
                            <td><strong>Solved:</strong> <?php echo $resolved; ?></td>
                            <td><strong>Unresolved:</strong> <?php echo $unresolved; ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>


<div id="myModal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close-btn">&times;</span>
        <p id="modal-text"></p>
    </div>
</div>
            <button onclick="exportTableToExcel('tblData')">Export to Excel</button>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <p>No records found.</p>
        <?php endif; ?>
    </div>

    <script>
        function exportTableToExcel(tableID, filename = 'TT Report.xls') {
            let table = document.getElementById(tableID);
            let html = table.outerHTML.replace(/ /g, '%20');
            let link = document.createElement("a");
            link.href = 'data:application/vnd.ms-excel,' + html;
            link.download = filename;
            link.click();
        }

        function openModal(text) {
            document.getElementById("modal-text").innerText = text;
            document.getElementById("myModal").style.display = "block";
        }

        document.getElementById("close-modal").addEventListener("click", function () {
            document.getElementById("myModal").style.display = "none";
        });

        window.onclick = function (event) {
            if (event.target == document.getElementById("myModal")) {
                document.getElementById("myModal").style.display = "none";
            }
        };
    </script>
</body>
</html>
