<?php
session_start();
// requestResetSession()
unset($_SESSION['LinkURL_id']);
unset($_SESSION['stored_LinkURL']);
unset($_SESSION['error']);
header("Location: InsertMethod.php?step=choose");
exit();
?>