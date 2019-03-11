<?php 
include "/var/www/fuelwatchapp.com/admin/config/config.php";
include $function_and_classes;
try {
    session($root_domain_config);
    $db = new database($home_sql, $dbhost_config, $dbusername_config, $dbpassword_config, $dbname_config);
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $user = new user;
        $register_status = $user->register($db, $register);
        if(isset($_SESSION['username'])) {
            header("Location: fuelwatchapp.php"); 
            exit;
        }
    }
}
catch (Exception $e) {
    header("Location: fuelwatchapp.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Fuel Watch - Register</title>
        <?php echo "<link rel='stylesheet' type='text/css' href='".$page_css."'>"?>
        <?php echo "<link rel='stylesheet' type='text/css' href='".$register_css."'>"?>   
    </head>
    <body id="docBody">
       <header id="bodyHeader" title="Main Content Header">
            <div id="bodyheadertitle">
                <h1>Fuel Watch.</h1>
            </div>
        </header>
        <nav id="navigation" title="Navigation"> 
                <h4 id="navHeading">Navigation</h4>
                <ul id="navList">
                        <li>
                            <a href="fuelwatchapp.php?action=home" title="Home">Home</a>
                        </li>
                        <?php 
                            if(empty($_SESSION['username'])) {
                                echo "<li><a href='fuelwatchapp.php?action=login' title='Login'>Login</a></li>";
                            }  
                        ?>
                </ul>
        </nav>
        <main id="mainContent">
            <div id="centerRegister">
                <h2>Register to Fuel Watch</h2>
                <form id="registerForm" title="register" action="fuelwatchapp.php" method="POST" role="form">
                    <div id="username">
                        <div class='fieldstyle'>
                            <label>Username</label>
                            <input type="text" name="username" required>
                        </div>
                    </div>
                    <div id="password">
                        <div class='fieldstyle'>
                            <label>Password</label>
                            <input type="text" name="password" required>
                        </div>    
                    </div>
                    <button name="register">Register</button>
                </form>
                <section id="registerStatus">
                <p><?php echo $register_status; ?></p>
                </section>
            </div>
        </main>
        <footer id="pagefooter">
                <h3>A.A.W</h3>
        </footer>
    </body>
</html>