
<?php
session_start();
function enforce_house_access($house_id) {
    if (!isset($_SESSION['house_id']) || $_SESSION['house_id'] != $house_id) {
        header('Location: /access_denied.php');
        exit;
    }
}
?>
