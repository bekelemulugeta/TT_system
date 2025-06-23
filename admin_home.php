<?php


require_once("adminn.php");
// Query for unresolved TT registrations
$re = '0000-00-00';
$queryyy = "SELECT branch_name, tt, lanip, tt_reg_date FROM `tt_registration` WHERE tt_resolved_date = ? ORDER BY tt_reg_date ASC";
$stmt = mysqli_prepare($link, $queryyy);
mysqli_stmt_bind_param($stmt, 's', $re);
mysqli_stmt_execute($stmt);
$result111 = mysqli_stmt_get_result($stmt);

// Check if there are any results
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="admin_home.css"> 
</head>
<body>
<?php
// Check if there are results and output the table
if (mysqli_num_rows($result111) > 0):


?>

<div class="container">
    <h1>Overview of Active TTs</h1>
    <table id="tblData" class="table table-bordered">
        <thead>
            <tr>
                <th>Branch Name</th>
                <th>TT</th>
                <th>LAN IP</th>
                <th>TT reg. date</th>
                <th>Days</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysqli_fetch_array($result111)):
                $today = new DateTime(); // Get current date
                $tt_date = new DateTime($row['tt_reg_date']); // TT registration date

                // Calculate the difference in days
                $interval = $today->diff($tt_date);
                $days = $interval->days;

                // Set color based on the number of days
                $day_color = 'green'; // Default color
                if ($days >= 3 && $days < 6) {
                    $day_color = '#efcc00'; // Yellow
                } elseif ($days >= 6) {
                    $day_color = 'red'; // Red
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['tt']); ?></td>
                    <td><?php echo htmlspecialchars($row['lanip']); ?></td>
                    <td><?php echo htmlspecialchars($row['tt_reg_date']); ?></td>
                    <td class="day-column" style="color:<?php echo $day_color; ?>;"><?php echo $days; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php
else:
    echo "No Active TT";
endif;

mysqli_close($link);
?>
</div>
</body>
</html>
