<?php
include "/var/www/html/fuelwatchapp.com/admin/config/config.php";

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['home'])) {
    include $home_view;
}
else if( ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['login'])) || ($_SERVER['REQUEST_METHOD'] == "GET" && ($_GET['action'] == "login")) ) {
    include $login_view;
}
else if( ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['register'])) || ($_SERVER['REQUEST_METHOD'] == "GET" && ($_GET['action'] == "register")) ) {
    include $register_view;
}
else if( ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['profile'])) || ($_SERVER['REQUEST_METHOD'] == "GET" && ($_GET['action'] == "profile")) ) {
    include $profile_view;
}
else if($_SERVER['REQUEST_METHOD'] == "GET" && $_GET['action'] == "logout" ) {
    include $logout_view;
}
else if($_SERVER['REQUEST_METHOD'] == "GET" && $_GET['action'] == "deleteaccount" ) {
    include $delete_view;
}
else {
    include $home_view;
}

?>