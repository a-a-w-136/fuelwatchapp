<?php
include "/var/www/html/fuelwatchapp.com/admin/config/config.php";
include $function_and_classes;
try {
    session($root_domain_config);
    $db = new database($home_sql, $dbhost_config, $dbusername_config, $dbpassword_config, $dbname_config);
    $user;
    if(!isset($_SESSION['username'])) {
        header("Location: fuelwatchapp.php"); 
        exit;
    }
    else if($_SERVER['REQUEST_METHOD'] == "POST"){
        $user = new Registereduser;
        $profile_status = $user->profilepost($db, $profile);
    }
    else {
        $user = new Registereduser;
        $profile_status = $user->profileget($db, $profile);
    }
}
catch (Exception $e) {
    header("Location: fuelwatchapp.php"); 
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Fuel Watch - Profile</title>
        <?php echo "<link rel='stylesheet' type='text/css' href='".$page_css."'>"?>
        <?php echo "<link rel='stylesheet' type='text/css' href='".$profile_css."'>"?>     
    </head>
    <body id="docBody" title="fuelwatch">
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
                            if(!empty($_SESSION['username'])) {
                                echo "<li><a href='fuelwatchapp.php?action=profile' title='Profile'>".$_SESSION['username']."</a></li>";
                            }  
                        ?>
                </ul>
        </nav>
        <main id="mainContent" title="mainContent">
            <div id="centerprofile">
                <div id="deleteprofile">
                    <h2>User<?php echo ": ".$_SESSION['username'] ?></h2>
                    <button title="Delete Account" type="button"><a href="fuelwatchapp.php?action=deleteaccount">Delete Account</a></button>
                </div>
                <section id="favourite">
                    <form title="setfavourite" action="fuelwatchapp.php" method="POST">
                                <h3>Favourite</h3>
                                <div id="setfavourite">
                                    <label>Fuel Type:</label>
                                    <select name="fuel">
                                        <?php 
                                        $fuel_types = $user->getfueltypes();
                                            foreach($fuel_types as $fuel_type) {
                                                if($_SERVER["REQUEST_METHOD"] == "POST" && $user->verifiedinput()) {
                                                    if($fuel_type == $user->postedfuel()) {
                                                       echo "<option value='$fuel_type' selected>" . $fuel_type . "</option>"; 
                                                    }
                                                    else {
                                                        echo "<option value='$fuel_type'>" . $fuel_type . "</option>";
                                                    }
                                                }
                                                else {
                                                    if($fuel_type == $user->postedfuel()) {
                                                       echo "<option value='$fuel_type' selected>" . $fuel_type . "</option>"; 
                                                    }
                                                    else {
                                                        echo "<option value='$fuel_type'>" . $fuel_type . "</option>";
                                                    }
                                                }
                                            }
                                        ?>
                                    </select>
                                        <?php 
                                            if($_SERVER["REQUEST_METHOD"] == "POST" && !($user->verifiedfueltype())) {
                                                    echo "<p>Error: ". $user->postedfuel() . " is not recognised.</p>";
                                                }
                                        ?>
                                </div>
                                <div id="searchtown">
                                    <label>Town:</label>
                                    <select name="locality_name">
                                        <?php 
                                          $locality_names = $user->getlocalitynames();
                                            foreach($locality_names as $locality_name) {
                                                if($_SERVER["REQUEST_METHOD"] == "POST" && $user->verifiedinput()) {
                                                    if($locality_name == $user->postedlocalityname()) {
                                                        echo "<option value='$locality_name' selected>" . $locality_name . "</option>";
                                                    }
                                                    else {
                                                        echo "<option value='$locality_name'>" . $locality_name . "</option>";
                                                    }
                                                }
                                                else {
                                                    if($locality_name == $user->postedlocalityname()) {
                                                        echo "<option value='$locality_name' selected>" . $locality_name . "</option>";
                                                    }
                                                    else {
                                                        echo "<option value='$locality_name'>" . $locality_name . "</option>";
                                                    }
                                                }
                                            }
                                        ?>
                                    </select>
                                        <?php 
                                            if($_SERVER["REQUEST_METHOD"] == "POST" && !($user->verifiedlocality())) {
                                                    echo "<p>Error: ". $user->postedlocality() . " is not recognised.</p>";
                                                }
                                        ?>
                                </div>
                                <div id="searchdistance">
                                    <label>Distance(km):</label>
                                    <select name="distance">
                                        <?php 
                                          $distances = $user->getdistances();
                                            foreach($distances as $distance) {
                                                if($_SERVER["REQUEST_METHOD"] == "POST" && $user->verifieddistance()) {
                                                    if($distance == $user->posteddistance()) {
                                                        echo "<option value='$distance' selected>" . $distance . "</option>";
                                                    }
                                                    else {
                                                        echo "<option value='$distance'>" . $distance . "</option>";
                                                    }
                                                }
                                                else {
                                                     if($distance == $user->posteddistance()) {
                                                        echo "<option value='$distance' selected>" . $distance . "</option>";
                                                    }
                                                    else {
                                                        echo "<option value='$distance'>" . $distance . "</option>";
                                                    }
                                                }
                                            }
                                        ?>
                                    </select>
                                        <?php 
                                            if($_SERVER["REQUEST_METHOD"] == "POST" && !($user->verifieddistance())) {
                                                    echo "<p>Error: ". $user->posteddistance() . " is not recognised.</p>";
                                                }
                                        ?>
                                </div>
                        <input type="submit" value="Submit Favourite" name="profile">
                    </form>
                </section>
            </div>
        </main>
        <footer id="pagefooter">
            <h3>A.A.W</h3>
        </footer>
    </body>
<?php $db = null;?>
</html>
