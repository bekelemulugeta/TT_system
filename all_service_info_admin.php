<?php
include_once("config.php");
include_once("adminn.php");

$query = "SELECT id, branch_name, service_number, service_type, bw, wanip, lanip FROM service_info ORDER BY branch_name ASC";
$result = mysqli_query($link, $query);
?>

<html>
<head>
    <title>Update Service Number</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link rel="stylesheet" href="all_service_info_admin.css"> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7-beta.19/jquery.inputmask.min.js"></script>

    <style>
        .hidden { display: none; }
        .loading { display: none; font-size: 14px; color: #ff0000; }
    </style>
</head>
<body>

<div class="container">
    <h2>Service Number List</h2>
    <table id="tblData" class="table table-bordered">
        <thead>
            <tr>
                <th>Branch Name</th>
                <th>Service Number</th>
                <th>Service Type</th>
                <th>Bandwidth</th>
                <th>WAN IP</th>
                <th>LAN IP</th>
                <th>Delete</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr id="row_<?php echo htmlspecialchars($row['id']); ?>">
                <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                <td><?php echo htmlspecialchars($row['service_number']); ?></td>
                <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                <td><?php echo htmlspecialchars($row['bw']); ?></td>
                <td><?php echo htmlspecialchars($row['wanip']); ?></td>
                <td><?php echo htmlspecialchars($row['lanip']); ?></td>
                <td><button class="btn btn-danger btn-sm remove" data-id="<?php echo $row['id']; ?>">Delete</button></td>
                <td><button class="edit btn btn-primary btn-sm" data-id="<?php echo $row['id']; ?>">Edit</button></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Edit Form -->
<div id="editFormContainer" class="hidden">
    <button class="x-btn" onclick="$('#editFormContainer').hide();">&times;</button>
    <h3>Edit Service Info</h3>
    <form id="editForm">
        <input type="hidden" name="id" id="editId" />
        
        <label>Branch Name:</label>
        <input type="text" name="branch_name" id="editBranchName" required />

        <label>Service Number:</label>
        <input type="number" name="service_number" id="editServiceNumber" required />

        <label>Service Type:</label>
        <select name="service_type" id="editServiceType" required>
            <option value="ADSL">ADSL</option>
            <option value="EPON">EPON</option>
            <option value="AIRONET">AIRONET</option>
            <option value="FIBER">FIBER</option>
            <option value="others">Other (please specify)</option>
        </select>
        <input type="text" id="otherServiceType" name="other_service_type" class="hidden" placeholder="Specify service type" />

        <label>Bandwidth:</label>
        <select name="bw" id="editBandwidth" required>
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
        <input type="text" id="otherBandwidth" name="other_bandwidth" class="hidden" placeholder="Specify bandwidth" />
        
        <label>WAN IP:</label>
        <input type="text" name="wanip" id="editWanip" class="mask-ipv4" required />
        
        <label>LAN IP:</label>
        <input type="text" name="lanip" id="editLanip" class="mask-ipv4" required />

        <input type="submit" value="Save Changes" class="btn btn-success" />
        <button type="button" class="btn btn-secondary" onclick="$('#editFormContainer').hide();">Cancel</button>
    </form>
    <p class="loading">Processing...</p>
</div>

<script>
    $(document).ready(function() {
        // Show/Hide Other Input Fields
        $("#editServiceType").change(function() {
            $("#otherServiceType").toggle($(this).val() === "others");
        });

        $("#editBandwidth").change(function() {
            $("#otherBandwidth").toggle($(this).val() === "Other");
        });

        // Edit Button Click Event
        $(".edit").click(function() {




            var id = $(this).data("id");
            $.get('fetch_service_data.php', { id: id }, function(response) {
                                
                try {
                    var data = JSON.parse(response);
                } catch (e) {
                    alert("Failed to fetch data. Invalid response.");
                    return;
                }
                $("#editId").val(data.id);
                $("#editBranchName").val(data.branch_name);
                $("#editServiceNumber").val(data.service_number);
                $("#editServiceType").val(data.service_type);
                $("#editBandwidth").val(data.bw);
                $("#editWanip").val(data.wanip);
                $("#editLanip").val(data.lanip);
                $("#editFormContainer").show();
            });
        });
// Delete Button Click Event
$(".remove").click(function() {
    var id = $(this).data("id");
    if (confirm('Are you sure you want to delete this service?')) {
        $.post('delete_service.php', { id: id })
            .done(function(response) {
                if (response === 'success') {
                    $("#row_" + id).fadeOut();
                } else {
                    alert('Error deleting service. Server response: ' + response);
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert('Request failed: ' + textStatus + '\nError: ' + errorThrown);
            });
    }
});

// Update Service Info
$("#editForm").submit(function(e) {
    e.preventDefault();
    $(".loading").show();
    
    $.post('update_service.php', $(this).serialize())
        .done(function(response) {
            if (response === 'success') {
                alert('Service updated successfully');
                $("#editFormContainer").hide();
                location.reload();
            } else {
                alert('Failed to update service. Server response: ' + response);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            alert('Update failed: ' + textStatus + '\nError: ' + errorThrown);
        })
        .always(function() {
            $(".loading").hide();
        });
});


        // Mask IP input
        $(".mask-ipv4").inputmask({ alias: "ip", greedy: false });
    });

    $(document).on("click", ".x-btn", function() {
    $("#editFormContainer").hide();
});

</script>

</body>
</html>
