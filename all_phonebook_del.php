<?php

include_once("config.php");
if(isset($_GET['id']))
{
     $sql = "DELETE FROM phonebook WHERE id=".$_GET['id'];
     $link->query($sql);
     echo 'Deleted successfully.';
}


?>