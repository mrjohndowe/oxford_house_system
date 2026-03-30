
<?php
function is_shared_file($path) {
    return strpos($path, 'state') !== false || strpos($path, 'chapter') !== false;
}
?>
