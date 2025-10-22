<?php


require_once("config.php");



if(!isset($_SESSION['login_user'])){
       session_destroy();
    header("location:login.php");
    exit;
   }


// Fetch user name from database
$stmt = $link->prepare("SELECT Name FROM user WHERE username = ?");
$stmt->bind_param("s", $_SESSION['login_user']);
$stmt->execute();
$resultr = $stmt->get_result();
$row1 = $resultr->fetch_array();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TT System</title>
    <link href="adminn.css" rel="stylesheet" type="text/css">
    <link rel="icon" type="ico" href="favicon.ico" sizes="16x16" />
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

     
   
</head>
<body>
  <!-- Sidebar Toggle Button for Mobile -->
    <button class="sidebar-toggle">☰</button>
    <div class="sidebar">
        <!-- Navbar -->
        <div class="navbar">
            <!-- Logo and Navigation Bar -->
            <a href="admin_home.php" class="navbar-item logo">
                <img src="GBE.PNG"  alt="Logo" class="logo-img">
            </a>
            
            <!-- PhoneBook Dropdown -->
            <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('phonebookDropdown')">PhoneBook <i class="fa-solid fa-circle-chevron-down"></i></button>
                <div id="phonebookDropdown" class="dropdown-content">
                    <a href="all_phonebook_admin.php">PhoneBook</a>
                    <a href="add_phonebook.php">Add Phone</a>
                </div>
            </div>

            <!-- Service Information Dropdown -->
            <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('serviceInfoDropdown')">Service Information <i class="fa-solid fa-circle-chevron-down"></i></button>
                <div id="serviceInfoDropdown" class="dropdown-content">
                    <a href="add_servicee_admin.php">Add Service Info</a>
                    <a href="all_service_info_admin.php">Update Service Info</a>
                    <a href="4g_ip_admin.php">4G Info</a>
                    <a href="4g_loan_admin.php">4G Loan</a>
                </div>
            </div>

            <a href="tt_reg_admin.php" class="navbar-item">Registration</a>
            <a href="down_branch_list.php" class="navbar-item">Bransh Status</a>

            <!-- TT Status Dropdown -->
            <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('ttStatusDropdown')">TT Status <i class="fa-solid fa-circle-chevron-down"></i></button>
                <div id="ttStatusDropdown" class="dropdown-content">
                    <a href="all_active_tts_admin.php">Active TTs</a>
                    <a href="active_tts_admin.php">Search Active TTs</a>
                    <a href="all_closed_tts_admin.php">Closed TTs</a>
                    <a href="search_closed_tts_by_name_admin.php">Search Closed TTs</a>
                </div>
            </div>

            <!-- HOIP Dropdown -->
            <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('headOfficeIpDropdown')">HOIP <i class="fa-solid fa-circle-chevron-down"></i></button>
                <div id="headOfficeIpDropdown" class="dropdown-content">
                    <a href="head_office_ip_list.php" class="w3-bar-item w3-button">IP Lists</a>
                    <a href="insert_record.php" class="w3-bar-item w3-button">Add HOIP</a>
                    <a href="add_department.php" class="w3-bar-item w3-button">Add Department</a>
                </div>
            </div>


            <!-- Report Dropdown -->
            <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('reportDropdown')">Report <i class="fa-solid fa-circle-chevron-down"></i></button>
                <div id="reportDropdown" class="dropdown-content">
                    <a href="all_ip.php" class="w3-bar-item w3-button">HO Computer</a>
                    <a href="all_tt_report.php" class="w3-bar-item w3-button">TT</a>
                    <a href="inventory.php" class="w3-bar-item w3-button">Inventory</a>
                    <a href="4g_report_admin.php" class="w3-bar-item w3-button">4G Loan</a>
                </div>
            </div>

 <?php if ($_SESSION['user_type'] == 'Admin'): ?>
                <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('manageUser')">Manage Users<i class="fa-solid fa-circle-chevron-down"></i></button>

                <div id="manageUser" class="dropdown-content">
            <a href="insert_user.php" class="navbar-item">Add User</a>
            <a href="del.php" class="navbar-item">Manage User</a>
            </div>
            </div>


            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('userProfileDropdown')">

                    <i class="fa-solid fa-user-plus"></i> <?php echo htmlspecialchars($row1[0], ENT_QUOTES, 'UTF-8'); ?>
                    
                    <i class="fa-solid fa-circle-chevron-down"></i>
                    <?php endif; ?>


                     <?php if ($_SESSION['user_type'] != 'Admin'): ?>
                <div class="dropdown">
                <button class="dropbtn" onclick="toggleDropdown('userProfileDropdown')">

                  <i class="fa-solid fa-user-minus"></i> <?php echo htmlspecialchars($row1[0], ENT_QUOTES, 'UTF-8'); ?>
                    <i class="fa-solid fa-circle-chevron-down"></i>
                            
            
                     <?php endif; ?>



                </button>
                <div id="userProfileDropdown" class="dropdown-content">
                    <a href="change_password_admin.php" class="dropdown-item">Change Password</a>
                    <a href="logout.php" class="dropdown-item">Sign Out</a>
                </div>
            </div>
        </div>

    </div>

    <!-- JS for dropdown toggle -->
    <script>
        function toggleDropdown(id) {
    // Close all dropdowns first
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
        if (dropdown.querySelector(".dropdown-content").id !== id) {
            dropdown.classList.remove("active");
        }
    });

    // Toggle the selected dropdown
    document.getElementById(id).parentElement.classList.toggle("active");
}

// Highlight active menu item
document.querySelectorAll(".dropdown-content a").forEach((link) => {
    link.addEventListener("click", function() {
        // Remove active class from all links
        document.querySelectorAll(".dropdown-content a").forEach((item) => item.classList.remove("active"));
        
        // Add active class to clicked link
        this.classList.add("active");
    });
});



          document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector(".sidebar");
            const toggleButton = document.querySelector(".sidebar-toggle");

            // Toggle the sidebar when the toggle button is clicked
            toggleButton.addEventListener("click", function (event) {
                sidebar.classList.toggle("expanded");
                event.stopPropagation(); // Prevent event from bubbling up
            });

            // Close sidebar when clicking outside
            document.addEventListener("click", function (event) {
                if (!sidebar.contains(event.target) && sidebar.classList.contains("expanded")) {
                    sidebar.classList.remove("expanded");
                }
            });
        });

    </script>

</body>
</html>
<?php include_once("footer.php");?>