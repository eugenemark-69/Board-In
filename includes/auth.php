<?php
require_once __DIR__ . '/../config/session.php';

function ensure_logged_in()
{
    require_login();
}

function ensure_role($roles)
{
    require_role($roles);
}

?>
