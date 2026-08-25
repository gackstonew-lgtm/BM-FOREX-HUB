<?php
/**
 * Signal Engine Configuration
 * Set SIGNAL_ENGINE_URL to your VPS IP address.
 * Example: putenv('SIGNAL_ENGINE_URL=http://YOUR-VPS-IP:5000');
 */
putenv('SIGNAL_ENGINE_URL=http://157.173.193.93:5000');

$PERMANENT_ADMIN_ACCESS = [
    'bonfacewana3072@gmail.com',
    'langatgift6@gmail.com',
    'gackstoneb@gmail.com',
];

define('DB_PATH', __DIR__ . '/storage/database.sqlite');

function bm_has_permanent_access($email) {
    global $PERMANENT_ADMIN_ACCESS;
    return is_string($email) && in_array(strtolower(trim($email)), $PERMANENT_ADMIN_ACCESS, true);
}
?>
