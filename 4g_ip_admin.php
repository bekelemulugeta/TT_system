<?php

require_once("adminn.php");

$success_message = '';
$error_message = '';

// Handle form submission for adding new 4G information
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
         header("location:login.php");
    }

    // Get form inputs
    $service_number = mysqli_real_escape_string($link, $_POST['service_number']);
    $ip = mysqli_real_escape_string($link, $_POST['ip']);
    $imei = mysqli_real_escape_string($link, $_POST['imei']);
    $sim_id = mysqli_real_escape_string($link, $_POST['sim_id']);
    
    // Insert query to add data into the database
    $query = "INSERT INTO `4g` (service_number, Ip, IMEI, sim_id) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $service_number, $ip, $imei, $sim_id);

    // Execute the query and check for success
    if (mysqli_stmt_execute($stmt)) {
        $success_message = 'Information added successfully!';
    } else {
        $error_message = 'Error adding record.';
    }
}

// Fetch current 4G information from the database
$queryyy = "SELECT id, service_number, Ip, IMEI, sim_id FROM `4g` ORDER BY Id ASC";
$result = mysqli_query($link, $queryyy);
?>

<html>
<head>
    <title>4G Information</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link rel="stylesheet" href="4g_list.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7-beta.19/jquery.inputmask.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Add Information</h2>

        <!-- Display Success or Error Message -->
        <?php if (!empty($success_message)): ?>
            <div class="alert success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>


        <!-- Add Information Form -->
        <form id="addForm" method="POST" class="form-inline">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" id="service_number" name="service_number" placeholder="Service Number" required>
            <input type="text" id="ip" name="ip" placeholder="IP" required>
            <input type="text" id="imei" name="imei" placeholder="IMEI" required>
            <input type="text" id="sim_id" name="sim_id" placeholder="SIM ID" required>
            <button type="submit" id="addBtn">Add</button>
        </form>

        <!-- Display Table of 4G Information -->
        <table id="tblData" class="table table-bordered">
            <thead>
                <tr>
                    <th>Service Number</th>
                    <th>IP</th>
                    <th>IMEI</th>
                    <th>SIM ID</th>
                    <th>Delete</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_array($result)) { ?>   
                <tr id="row_<?php echo $row['id']; ?>">
                    <td><?php echo $row['service_number']; ?></td> 
                    <td><?php echo $row['Ip']; ?></td> 
                    <td><?php echo $row['IMEI']; ?></td>  
                    <td><?php echo $row['sim_id']; ?></td>  
                    <td><button class="btn-delete" data-id="<?php echo $row['id']; ?>">Delete</button></td>
                    <td><button class="btn-edit" data-id="<?php echo $row['id']; ?>">Edit</button></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Form (Initially Hidden) -->
    <div id="editFormContainer" style="display: none;">
        <button class="x-btn" onclick="$('#editFormContainer').hide();">&times;</button> <!-- Red X button -->
        <h3>Edit Information</h3>
        <form id="editForm">
            <input type="hidden" id="edit_id">
            <input type="text" id="edit_service_number" placeholder="Service Number">
            <input type="text" id="edit_ip" class="mask-ipv4" placeholder="IP">
            <input type="text" id="edit_imei" placeholder="IMEI">
            <input type="text" id="edit_sim_id" placeholder="SIM ID">
            <button type="button" id="saveEdit">Save</button>
            <button type="button" class="btn btn-secondary" onclick="$('#editFormContainer').hide();">Cancel</button>
        </form>
    </div>
    </body>
</html>

<script>
    $(document).ready(function() {
        $(".btn-delete").click(function() {
            var id = $(this).data("id");
            if(confirm('Are you sure to delete this information?')) {
                $.ajax({
                    url: '4g_del.php',
                    type: 'POST',
                    data: {id: id},
                    success: function(response) {
                        if(response.trim() === "Success") {
                            $("#row_" + id).remove();
                        } else {
                            alert("Error deleting record.");
                        }
                    }
                });
            }
        });

        $(".btn-edit").click(function() {
            var id = $(this).data("id");
            var row = $("#row_" + id);
            $("#edit_id").val(id);
            $("#edit_service_number").val(row.find("td:eq(0)").text());
            $("#edit_ip").val(row.find("td:eq(1)").text());
            $("#edit_imei").val(row.find("td:eq(2)").text());
            $("#edit_sim_id").val(row.find("td:eq(3)").text());
            $("#editFormContainer").show();
        });

        $("#saveEdit").click(function() {
            var id = $("#edit_id").val();
            var service_number = $("#edit_service_number").val();
            var ip = $("#edit_ip").val();
            var imei = $("#edit_imei").val();
            var sim_id = $("#edit_sim_id").val();

            $.ajax({
                url: '4g_edit.php',
                type: 'POST',
                data: {
                    id: id,
                    service_number: service_number,
                    ip: ip,
                    imei: imei,
                    sim_id: sim_id
                },
                success: function(response) {
                    if(response.trim() === "Success") {
                        var row = $("#row_" + id);
                        row.find("td:eq(0)").text(service_number);
                        row.find("td:eq(1)").text(ip);
                        row.find("td:eq(2)").text(imei);
                        row.find("td:eq(3)").text(sim_id);
                        $("#editFormContainer").hide();
                    } else {
                        alert("Error updating record.");
                    }
                }
            });
        });

        $(".mask-ipv4").inputmask({ alias: "ip", greedy: false });
    });

 setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.style.display = 'none');
    }, 3000);

    
</script>


