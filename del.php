<?php
include_once('adminn.php');
include_once('config.php');

if ($_SESSION['user_type'] == 'User'){
       session_destroy();
    header("location:login.php");
    exit;
 
 }
// Fetching user data
$sql = "SELECT * FROM user";
$users = $link->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="manage_user_style.css">
    
</head>

<body>
    
    <div class="container">
        <h1>Manage Users</h1>
         <table id="tblData" class="table table-bordered">
            <thead>
                <tr>
                    
                    <th>User Name</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Delete</th>
                    <th>Reset Password</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr id="user-<?php echo $user['id']; ?>">
                        <td><?php echo htmlspecialchars ($user['username']); ?></td>
                        <td><?php echo htmlspecialchars ($user['email']); ?></td>
                        <td><?php echo htmlspecialchars ($user['user_type']); ?></td>
                        <td><button class="btn btn-danger btn-sm remove" data-id="<?php echo $user['id']; ?>">Delete</button></td>
                        <td><button class="btn btn-warning btn-sm reset" data-id="<?php echo $user['id']; ?>">Reset</button></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div> <!-- container / end -->

    <script type="text/javascript">
        // Delete User
        $(".remove").click(function() {
            var id = $(this).data("id");
            if (confirm('Are you sure you want to delete this user?')) {
                $.ajax({
                    url: 'delete_user.php',
                    type: 'GET',
                    data: { id: id },
                    success: function(data) {
             
                        $("#user-" + id).remove();
                        alert("User deleted successfully.");
                    },
                    error: function() {
                        alert('Something went wrong.');
                    }
                });
            }
        });

        // Reset Password
        $(".reset").click(function() {
            var id = $(this).data("id");
            if (confirm('Are you sure you want to reset this user?')) {
                $.ajax({
                    url: 'reset_user.php',
                    type: 'GET',
                    data: { id: id },
                    success: function(data) {
                        alert("Password reset successfully. Temporary password: gbe@123");
                    },
                    error: function() {
                        alert('Something went wrong.');
                    }
                });
            }
        });
    </script>
</body>
</html>
