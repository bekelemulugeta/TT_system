
<?php
include_once("config.php");
include_once("adminn.php");

$queryyy =  "SELECT branch_name, lanip FROM `service_info` ORDER BY id DESC";
$result = mysqli_query($link, $queryyy);
$message = "";
$message_type = "";

// Function to sanitize input
function clean_input($link, $input) {
    return mysqli_real_escape_string($link, htmlspecialchars(trim($input)));
}

if (isset($_POST['but_submit'])) {
    $branch_name = clean_input($link, $_POST['branch_name']);
    $service_number = clean_input($link, $_POST['service_number']);
    $wanip = clean_input($link, $_POST['wanip']);
    
    // Append "/24" only if it's a valid IP
    $lanip = filter_var($_POST['lanip'], FILTER_VALIDATE_IP) ? clean_input($link, $_POST['lanip']) . "/24" : "";

// Check if 'bw' is set before using it
    $bww = isset($_POST['bw']) ? clean_input($link, $_POST['bw']) : '';
    $bw = ($bww === "Other") ? clean_input($link, $_POST['bw_other']) : $bww;

    // Check if 'service_typee' is set before using it
    $service_typee = isset($_POST['service_typee']) ? clean_input($link, $_POST['service_typee']) : '';
    $service_type = ($service_typee === "others") ? clean_input($link, $_POST['service_type']) : $service_typee;


    // Query to check for duplicates
    $query = "SELECT lanip, branch_name FROM service_info WHERE lanip = ? OR branch_name = ? OR service_number = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "sss", $lanip, $branch_name, $service_number);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
       
        $message = "IP, Branch Name, or Service Number is already assigned. Please change it.";
        $message_type = "error";
    } else {
        // Insert into service_info
        $insertQuery = "INSERT INTO service_info (branch_name, service_number, service_type, bw, wanip, lanip) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = mysqli_prepare($link, $insertQuery);
        mysqli_stmt_bind_param($stmtInsert, "ssssss", $branch_name, $service_number, $service_type, $bw, $wanip, $lanip);
        
        if (mysqli_stmt_execute($stmtInsert)) {
             $message = "Information added successfully!";
                $message_type = "success";
        } else {
          $message = "Error adding service information. Try again.";
            $message_type = "error";
            mysqli_stmt_close($stmtInsert);
    mysqli_stmt_close($stmtDgb);
        }
    }
    mysqli_stmt_close($stmt);
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service Info</title>
    <link href="add_service.css" rel="stylesheet" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
</head>
<body>

<div class="container">
    
    <!-- Table Section -->
    <div class="table-container">
        <table >
            <thead>
                <tr>
                    <th >Branch Name</th>  
                    <th>LAN IP</th> 
                </tr>  
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_array($result)) { ?>   
                    <tr>
                        <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['lanip']); ?></td>
                    </tr>  
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Form Section -->
    <div class="form-container">
        <h1>Add Service Information</h1>
    <!-- Display Success/Error Messages -->
                <?php if (!empty($message)): ?>
                    <div class="message <?= htmlspecialchars($message_type) ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
        <form method="post" action="">

            <label>Branch Name:</label>
            <input type="text" name="branch_name" required />

            <label>Service Number:</label>
            <input type="number" name="service_number" />

            <label>Select Service Type:</label>
            <select name="service_typee" id="service_typee" onchange="toggleServiceType(this.value)">
                <option value="" selected disabled hidden>Please select ...</option>
                <option value="ADSL">ADSL</option>
                <option value="EPON">EPON</option>
                <option value="AIRONET">AIRONET</option>
                <option value="FIBER">FIBER</option>
                <option value="others">Other (please specify)</option>
            </select>
            <input type="text" name="service_type" id="service_type" class="optional-input" style="display:none;" />

            <label>Select Bandwidth:</label>
            <select name="bw" id="bw" onchange="toggleBandwidth(this.value)">
                <option value="" selected disabled hidden>Please select ...</option>
                <option value="1MB">1MB</option>
                <option value="2MB">2MB</option>
                <option value="3MB">3MB</option>
                <option value="4MB">4MB</option>
                <option value="5MB">5MB</option>
                <option value="10MB">10MB</option>
                <option value="20MB">20MB</option>
                <option value="100MB">100MB</option>
                <option value="Other">Other (please specify)</option>
            </select>
            <input type="text" name="bw_other" id="bw_other" class="optional-input" style="display:none;" />

            <label>WAN IP Address:</label>
            <input type="text" class="mask-ipv4" name="wanip" placeholder="xxx.xxx.xxx.xxx" />

            <label>LAN IP Address:</label>
            <input type="text" class="mask-ipv4" name="lanip" placeholder="xxx.xxx.xxx.xxx" required />

            <input type="submit" value="Submit" name="but_submit" />
        </form>
    </div>
</div>

<script>
    // Mask for IP addresses
    $('.mask-ipv4').inputmask({ alias: "ip", greedy: false });

    // Function to toggle Service Type input field visibility
    function toggleServiceType(value) {
        if (value === "others") {
            document.getElementById("service_type").style.display = "block";
        } else {
            document.getElementById("service_type").style.display = "none";
        }
    }

    // Function to toggle Bandwidth input field visibility
    function toggleBandwidth(value) {
        if (value === "Other") {
            document.getElementById("bw_other").style.display = "block";
        } else {
            document.getElementById("bw_other").style.display = "none";
        }
    }
</script>

</body>
</html>
