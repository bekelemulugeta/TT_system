
<?php
require_once("adminn.php");


// Fetch departments for dropdown
$query = "SELECT department FROM `department` ORDER BY id ASC";
$departments = mysqli_query($link, $query);

// Fetch department data on form submission
// Fetch department data on form submission
$Dep = '';
$records = [];
$result = null; // Fix 1: Initialize $result

if (isset($_POST['submitt'])) {
    $Dep = isset($_POST['branch']) ? mysqli_real_escape_string($link, $_POST['branch']) : '';

    $queryyy = "SELECT id, User_Name, Department, Computer_Model, Computer_Name, Ip, Flex, Rep, Local, NBE, Internet 
                FROM `head_office_ip` WHERE Department = ? ORDER BY Id ASC";

    if (!empty($Dep)) {
        $stmt = mysqli_prepare($link, $queryyy);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $Dep);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        }
    }

    if ($result) { // Fix 2: Prevent errors when $result is null
        while ($row = mysqli_fetch_assoc($result)) {
            $records[] = $row;
        }
    }

    if ($stmt) { // Fix 3: Only close if $stmt is initialized
        mysqli_stmt_close($stmt);
    }
}

// Fix 4: Reset pointer before second use
mysqli_data_seek($departments, 0);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update User of Head Office</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7-beta.19/jquery.inputmask.min.js"></script>
<link rel="stylesheet" href="head_office_ip_list.css">
</head>
<body>

<div class="container">
     <h1>HOIP List</h1>

<!-- Department Selection Form -->
<div class="form-container">
   
<form method="post" action="">
    
        <select name="branch" required>
            <option value="" selected disabled hidden>Please select department...</option>
            <?php while ($row = mysqli_fetch_array($departments)) : ?>
                <option value="<?= htmlspecialchars($row['department']) ?>" <?= ($Dep == $row['department']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['department']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="submit" value="Submit" name="submitt">
   
</form>
</div>
<?php if (!empty($records)) : ?>
    <div class="table-container">
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
                <th>Local</th>
                <th>NBE</th>
                <th>Internet</th>
                <th>Action</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $row) : ?>
                <tr id="row_<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row['User_Name']) ?></td>
                    <td><?= htmlspecialchars($row['Department']) ?></td>
                    <td><?= htmlspecialchars($row['Computer_Model']) ?></td>
                    <td><?= htmlspecialchars($row['Computer_Name']) ?></td>
                    <td><?= htmlspecialchars($row['Ip']) ?></td>
                    <td><?= htmlspecialchars($row['Flex']) ?></td>
                    <td><?= htmlspecialchars($row['Rep']) ?></td>
                    <td><?= htmlspecialchars($row['Local']) ?></td>
                    <td><?= htmlspecialchars($row['NBE']) ?></td>
                    <td><?= htmlspecialchars($row['Internet']) ?></td>
                    <td><button class="remove btn btn-danger btn-sm" data-id="<?= $row['id'] ?>">Delete</button></td>
                    <td><button class="edit btn btn-primary btn-sm" data-id="<?= $row['id'] ?>">Edit</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
    <!-- Edit Form -->
    
     <!-- Edit Form (Initially Hidden) -->
<div id="editFormContainer" style="display: none;">
    <button class="x-btn" onclick="$('#editFormContainer').hide();">&times;</button> <!-- Red X button -->
        <h3>Edit Record</h3>
        <form id="updateForm">
            
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <input type="hidden" name="id" id="edit_id">
            <label>User Name:</label> <input type="text" name="User_Name" id="edit_User_Name">
            <label>Department:</label>
            <select name="Department" id="edit_Department">
                <?php mysqli_data_seek($departments, 0);
                while ($row = mysqli_fetch_array($departments)) : ?>
                    <option value="<?= htmlspecialchars($row['department']) ?>">
                        <?= htmlspecialchars($row['department']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label>Computer Model:</label> <input type="text" name="Computer_Model" id="edit_Computer_Model">
            <label>Computer Name:</label> <input type="text" name="Computer_Name" id="edit_Computer_Name">
            <label>IP:</label> <input type="text" name="Ip" id="edit_Ip" class="mask-ipv4">
            
            <?php
            $fields = ['Flex', 'Rep', 'Local', 'NBE', 'Internet'];
            foreach ($fields as $field) : ?>
                <label><?= $field ?>:</label>
                <select name="<?= $field ?>" id="edit_<?= $field ?>">
                    <option value="Y">Yes</option>
                    <option value="N">No</option>
                </select>
            <?php endforeach; ?>

              <input type="submit" value="Save Changes" class="btn btn-success" />
              <button type="button" class="btn btn-secondary" onclick="$('#editFormContainer').hide();">Cancel</button>
        </form>
    </div>
</div>

<script>
  
  
$(document).ready(function () {
    // Handle edit button click
    $(".edit").click(function () {
        var id = $(this).data("id");

        $.ajax({
            url: "fetch_record.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function (data) {
                if (data.error) {
                    alert("Error: " + data.error);
                } else {
                    $("#edit_id").val(data.id);
                    $("#edit_User_Name").val(data.User_Name);
                    $("#edit_Department").val(data.Department);
                    $("#edit_Computer_Model").val(data.Computer_Model);
                    $("#edit_Computer_Name").val(data.Computer_Name);
                    $("#edit_Ip").val(data.Ip);
                    $("#edit_Flex").val(data.Flex);
                    $("#edit_Rep").val(data.Rep);
                    $("#edit_Local").val(data.Local);
                    $("#edit_NBE").val(data.NBE);
                    $("#edit_Internet").val(data.Internet);
                    $("#editFormContainer").fadeIn();
                }
            },
            error: function () {
                alert("Error fetching data.");
            }
        });
    });

    // Handle update form submission
    $("#updateForm").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "update_record.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert("Updated successfully!");
                    location.reload(); // Reload to reflect changes
                } else {
                    alert("Error: " + response.error);
                }
            },
            error: function () {
                alert("Error updating record.");
            }
        });
    });

    // Hide edit form when cancel button is clicked
    $(".btn-secondary").click(function () {
        $("#editFormContainer").fadeOut();
    });
});



    $(".remove").click(function() {
        var id = $(this).data("id");
        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                url: "delete_record.php",
                type: "POST",
                data: { id: id },
                success: function() {
                    $("#row_" + id).remove();
                }
            });
        }
    });

 // Close the form when X button is clicked
$(".x-btn").click(function() {
    $("#editFormContainer").hide();
});

 // Mask IP input
        $(".mask-ipv4").inputmask({ alias: "ip", greedy: false });
       
</script>

</body>
</html>
