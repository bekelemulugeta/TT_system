<?php
include_once("config.php");
include_once("adminn.php");

// Fetch phonebook data
$queryyy = "SELECT id, branch_name, office, manager, mphone, accountant, aphone FROM `phonebook` ORDER BY branch_name ASC";
$result = mysqli_query($link, $queryyy);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Book</title>
    <link rel="stylesheet" href="all_phone_admin.css">
  
   
</head>
<body>

<div class="container">
    <h1>PhoneBook</h1>
    <table id="tblData" class="table table-bordered">
        <thead>
            <tr>
                <th>Branch Name</th>
                <th>Office Phone</th>
                <th>Manager</th>
                <th>Manager Phone</th>
                <th>Accountant</th>
                <th>Accountant Phone</th>
                <th>Delete</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['office']); ?></td>
                    <td><?php echo htmlspecialchars($row['manager']); ?></td>
                    <td><?php echo htmlspecialchars($row['mphone']); ?></td>
                    <td><?php echo htmlspecialchars($row['accountant']); ?></td>
                    <td><?php echo htmlspecialchars($row['aphone']); ?></td>
                    <td><button class="btn btn-danger btn-sm remove" data-id="<?php echo $row['id']; ?>">Delete</button></td>
                    <td><button class="edit btn btn-primary btn-sm" data-id="<?= $row['id'] ?>" 
                        data-branch="<?php echo htmlspecialchars($row['branch_name']); ?>"
                        data-office="<?php echo htmlspecialchars($row['office']); ?>"
                        data-manager="<?php echo htmlspecialchars($row['manager']); ?>"
                        data-mphone="<?php echo htmlspecialchars($row['mphone']); ?>"
                        data-accountant="<?php echo htmlspecialchars($row['accountant']); ?>"
                        data-aphone="<?php echo htmlspecialchars($row['aphone']); ?>"
                    >Edit</button></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="export-button">
        <button onclick="exportTableToExcel('tblData')">Export Table Data To Excel File</button>
        <a href="add_phonebook.php" class="btn btn-secondary">Go to Add Phone</a>
    </div>

    
   <!-- Edit Form (Initially Hidden) -->
<div id="editFormContainer" style="display: none;">
    <button class="x-btn" onclick="$('#editFormContainer').hide();">&times;</button> <!-- Red X button -->
    <h3>Edit Phonebook Entry</h3>
    <form id="editForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" id="edit_id">
        <label>Branch Name:</label>
        <input type="text" id="edit_branch" required>
        <label>Office Phone:</label>
        <input type="text" id="edit_office" required>
        <label>Manager:</label>
        <input type="text" id="edit_manager" required>
        <label>Manager Phone:</label>
        <input type="text" id="edit_mphone" required>
        <label>Accountant:</label>
        <input type="text" id="edit_accountant" required>
        <label>Accountant Phone:</label>
        <input type="text" id="edit_aphone" required>
        <button type="submit" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-secondary" onclick="$('#editFormContainer').hide();">Cancel</button>
    </form>
</div>


</div>

<script type="text/javascript">
    // Delete phonebook entry
    $(".remove").click(function(){
        var id = $(this).data("id");
        if (confirm('Are you sure to delete this phonebook entry?')) {
            $.ajax({
                url: 'all_phonebook_del.php',
                type: 'GET',
                data: { id: id },
                success: function() {
                    $("#row-" + id).remove();
                    alert("Information deleted successfully");
                },
                error: function() {
                    alert('Something went wrong.');
                }
            });
        }
    });

 // Close the form when X button is clicked
$(".x-btn").click(function() {
    $("#editFormContainer").hide();
});

// Show the edit form when edit button is clicked
$(".edit").click(function() {
    $("#edit_id").val($(this).data("id"));
    $("#edit_branch").val($(this).data("branch"));
    $("#edit_office").val($(this).data("office"));
    $("#edit_manager").val($(this).data("manager"));
    $("#edit_mphone").val($(this).data("mphone"));
    $("#edit_accountant").val($(this).data("accountant"));
    $("#edit_aphone").val($(this).data("aphone"));
    $("#editFormContainer").show();
});

    // Handle form submission for update
    $("#editForm").submit(function(event){
        event.preventDefault();
        var formData = {
            id: $("#edit_id").val(),
            branch_name: $("#edit_branch").val(),
            office: $("#edit_office").val(),
            manager: $("#edit_manager").val(),
            mphone: $("#edit_mphone").val(),
            accountant: $("#edit_accountant").val(),
            aphone: $("#edit_aphone").val()
        };

        $.ajax({
            url: 'update_phonebook.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert("Information updated successfully");
                location.reload(); // Refresh the page to show updated data
            },
            error: function() {
                alert('Update failed.');
            }
        });
    });

    // Export table data to Excel
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

        filename = filename ? filename + '.xls' : 'excel_data.xls';

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
</script>

</body>
</html>
