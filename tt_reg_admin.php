
<?php
require_once("adminn.php");


// Initialize variables
$searchResults = [];
$searchTerm = '';
$error_message = '';

// Check if search is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: login.php");
    }
    $searchTerm =htmlspecialchars(trim(mysqli_real_escape_string($link, $_POST['search'])));
    $query = "SELECT branch_name, service_number, service_type, bw, wanip, lanip 
              FROM `service_info` 
              WHERE (((branch_name LIKE '%" . $searchTerm . "%') 
                      OR (service_number LIKE '%" . $searchTerm . "%')) 
                     OR (lanip LIKE '%" . $searchTerm . "%')) 
              ORDER BY branch_name ASC";
    $result = mysqli_query($link, $query);

    // Fetch search results
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $searchResults[] = $row;
        }
    }
}

// Handle TT Registration
if (isset($_POST['but_submit'])) {
    // Registration logic goes here
    $branch_namee = mysqli_real_escape_string($link, $_POST['branch_name']);
    $TT = $_POST['TT'];
    $resolved = '0000-00-00';
    $date = date("Y-m-d");

    // Validate TT (Numeric and length 16)
    if (!preg_match("/^\d{16}$/", $TT)) {
        $error_message = "TT must be exactly 16 digits.";
    } else {
        // Check if TT is already registered
        $sql_quer = "SELECT branch_name FROM `tt_registration` WHERE tt_resolved_date = ? AND branch_name = ?";
        if ($stmt = mysqli_prepare($link, $sql_quer)) {
            mysqli_stmt_bind_param($stmt, 'ss', $resolved, $branch_namee);
            mysqli_stmt_execute($stmt);
            $result1 = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result1) > 0) {
          $error_message = $branch_namee . " is already registered. Check it here: <a href='all_active_tts_admin.php'>clike me</a>";


            } else {
                // Fetch branch details
                $sql_queryy = "SELECT branch_name, service_number, wanip, lanip FROM `service_info` WHERE branch_name = ?";
                if ($stmt2 = mysqli_prepare($link, $sql_queryy)) {
                    mysqli_stmt_bind_param($stmt2, 's', $branch_namee);
                    mysqli_stmt_execute($stmt2);
                    $result2 = mysqli_stmt_get_result($stmt2);
                    $row = mysqli_fetch_array($result2);

                    // Register TT
                    $Reg_by = "Registered by " . $_SESSION['login_user'];

                    // Insert query execution
                    $sql = "INSERT INTO tt_registration (branch_name, service_number, tt, wanip, lanip, tt_reg_date, tt_resolved_date, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    if ($stmt3 = mysqli_prepare($link, $sql)) {
                        mysqli_stmt_bind_param($stmt3, 'ssssssss', $row['branch_name'], $row['service_number'], $TT, $row['wanip'], $row['lanip'], $date, $resolved, $Reg_by);

                        if (mysqli_stmt_execute($stmt3)) {
                            $success_message =$branch_namee . " registered successfully.";
                        } else {
                            $error_message = "Something went wrong, it could be a database issue.";
                        }
                    }
                }
            }
        }
    }
}
?>

<html>
<head>
    <meta charset="utf-16">
    <title>TT Registration</title>
    <link href="tt_reg_admin.css" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
</head>
<body>
    <div class="container">
        
            <?php if (!empty($error_message)): ?>
        <div class="error">
            <p><?php echo $error_message; ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <p><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <?php endif; ?>


        <form method="post" action="">
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div id="search">
                <h1>TT Registration</h1>

                <!-- Search Section -->
                <div>
                    <input id="texta" name="search" type="text" placeholder="Branch Name, SN, LAN IP" class="search-input">
                    <button class="fa fa-search" type="submit" name="submit" class="search-button">Search</button>
                </div>

                <!-- Display search results if any -->
                <?php
                if (!empty($searchResults)) {
                ?>
                    <h3>Search Results</h3>
                     <table id="tblData" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Branch Name</th>
                                <th>Service Number</th>
                                <th>Service Type</th>
                                <th>Bandwidth</th>
                                <th>WAN IP</th>
                                <th>LAN IP</th>
                                <th>Register</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($searchResults as $row) {
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['branch_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['service_number'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['service_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['bw'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['wanip'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['lanip'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <button type="button" class="register-btn" onclick="showRegisterForm('<?php echo $row['branch_name']; ?>')">Register</button>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>

                   
                <?php
                } else {
                    if (isset($_POST['submit'])){
                    echo "<p>No results found for " . $searchTerm . "</p>";

                }}
                ?>
            </div>
        </form>
    </div>

 <!-- Registration Form -->
                       <!-- Edit Form (Initially Hidden) -->
<div id="editFormContainer" style="display: none;">
    <button class="x-btn" onclick="$('#editFormContainer').hide();">&times;</button> <!-- Red X button -->
                        <h3>Register TT</h3>
                        <form method="post" action="">
                            <label >Branch Name:</label>
                            <input type="text" name="branch_name" id="branch_name" readonly />
                            <label >Enter TT:</label>
                            <input type="text" name="TT" id="ttt" maxlength="16" minlength="16" required pattern="^\d{16}$" />
                            <small class="error-message" id="tt_error"></small>
                            <input type="submit" value="Register" name="but_submit" id="but_submit" />

                   

                        <button type="button" class="btn btn-secondary" onclick="$('#editFormContainer').hide();">Cancel</button>
                    </div>
</form>

    <script>
        function showRegisterForm(branchName) {
            // Display the registration form and populate the branch name field
            document.getElementById('editFormContainer').style.display = 'block';
            document.getElementById('branch_name').value = branchName;
        }

        function closeForm() {
            // Hide the registration form
            document.getElementById('registerForm').style.display = 'none';
        }

        // Input validation for TT field (only numbers, 16 digits)
        var ttInput = document.getElementById("ttt");
        var errorMessage = document.getElementById("tt_error");

        ttInput.addEventListener("input", function () {
            var value = ttInput.value;
            ttInput.value = value.replace(/\D/g, ''); // Allow only numbers

            if (ttInput.value.length !== 16) {
                errorMessage.textContent = "TT must be exactly 16 digits.";
            } else {
                errorMessage.textContent = "";
            }
        });

         // Close the form when X button is clicked
$(".x-btn").click(function() {
    $("#editFormContainer").hide();
});

setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.style.display = 'none');
    }, 3000);



    </script>
</body>
</html>
