<?php   
include_once("config.php");
include_once("adminn.php");

// Use prepared statements to prevent SQL injection
$queryyy = "SELECT id, User_Name, Department, Computer_Model, Computer_Name, Ip, Flex, Rep, Local, NBE, Internet FROM head_office_ip ORDER BY Department ASC";
$stmt = mysqli_prepare($link, $queryyy);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$quer = "SELECT count(*) as alll FROM head_office_ip";
$resultt = mysqli_query($link, $quer);
$alll = mysqli_fetch_array($resultt);
$all = htmlspecialchars($alll['alll']);
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User of Head Office</title>
    <link rel="stylesheet" href="all_ip.css"> <!-- External CSS -->
   
</head>
<body>
    <div class="container">
        <h2>HO Computer List</h2>
    <table id="tblData" class="table table-bordered">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Department</th>
                <th>Computer Model</th>
                <th>Computer Name</th>
                <th>IP</th>
                <th>Flex</th>
                <th>Report</th>
                <th>40/42</th>
                <th>NBE</th>
                <th>Internet</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr id="<?php echo htmlspecialchars($row['id']); ?>">
                    <td><?php echo htmlspecialchars($row['User_Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Department']); ?></td>
                    <td><?php echo htmlspecialchars($row['Computer_Model']); ?></td>
                    <td><?php echo htmlspecialchars($row['Computer_Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Ip']); ?></td>
                    <td><?php echo htmlspecialchars($row['Flex']); ?></td>
                    <td><?php echo htmlspecialchars($row['Rep']); ?></td>
                    <td><?php echo htmlspecialchars($row['Local']); ?></td>
                    <td><?php echo htmlspecialchars($row['NBE']); ?></td>
                    <td><?php echo htmlspecialchars($row['Internet']); ?></td>
                    <td><button class="btn btn-danger btn-sm remove">Delete</button></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <td><?php echo $all; ?></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="export-container">
        <button onclick="exportTableToExcel('tblData')">Export Table Data To Excel File</button>
    </div>
    </div>
    <script src="all_ip.js"></script> <!-- External JS -->
</body>
</html>
