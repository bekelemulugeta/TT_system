<?php
   session_start();
   
   if(session_destroy()) {

      header("Location: login.php");

   }
?>

<!DOCTYPE html>
<html>
<head>
 <title>Logout page</title>
 <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
<style>
.vertical-menu {
  width: 10px;
  margin-right: 0px;
}

.vertical-menu a {
  background-color: #eee;
  color: black;
  display: block;
  padding: 12px;
  text-decoration: none;
}

  }
.vertical-menu a:visited {
  color: purple;
}
.vertical-menu a:link { /* Essentially means a[href], or that the link actually goes somewhere */
  color: blue;
}

.vertical-menu a:hover {
  background-color: #ccc;
}

.vertical-menu a.active {
  background-color: #4CAF50;
  color: white;

}

</style>
</head>
<body class="loggedin">
<nav class="navtop">
<div class="vertical-menu">
  <a href="logout.php"> <i class="fas fa-sign-out-alt"></i>logout</a>
</div>
  
</nav>

</body>
</html>
