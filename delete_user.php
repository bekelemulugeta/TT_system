<?php


include_once("config.php");


if(isset($_GET['id']))
{
$sql = "SELECT username FROM user WHERE id=".$_GET['id'];
$resultt = mysqli_query($link,$sql);
        $roww = mysqli_fetch_array($resultt);
        $ut=$roww['username'];
       
if ($ut==$_SESSION['login_user']) {
echo 'You are trying to delete your own account it is impossible!!!';
}
else{
     $sql = "DELETE FROM user WHERE id=".$_GET['id'];
     $link->query($sql);
     echo 'Deleted successfully.';
}
}
?>