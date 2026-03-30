
<?php
function get_starting_balance($pdo, $table, $house_id) {
    $stmt = $pdo->prepare("SELECT ending_balance FROM $table WHERE house_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$house_id]);
    $row = $stmt->fetch();
    return $row ? $row['ending_balance'] : 0.00;
}
?>
