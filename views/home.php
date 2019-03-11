<?php
include "/var/www/fuelwatchapp.com/admin/config/config.php";
include $function_and_classes;
try {
    session($root_domain_config);
    $db = new database($home_sql, $dbhost_config, $dbusername_config, $dbpassword_config, $dbname_config);
    $user;
    $cheapest_fuel = false;
    $favourite = false;
    if($_SERVER["REQUEST_METHOD"] == "GET" && !isset($_SESSION['username'])) {
        $user = new User;
        $user->homepageget($db, $home);
        $cheapest_fuel = true;
    }
    else if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user = new User;
        $user->homepagepost($db, $home);
    }
    else if(isset($_SESSION['username']) && $_SERVER["REQUEST_METHOD"] == "GET") {
        $user = new Registereduser;
        $favourite_status = $user->profileget($db, $home);
        if(!($user->favourite())) {
            $cheapest_fuel = true;
        }
        else {
            $favourite = true;
        }
    }
}
catch (Exception $e) {
//    echo $e->getMessage();
    header("Location: fuelwatchapp.php"); 
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="references" content="https://www.fuelwatch.wa.gov.au/">
        <title>Fuel Watch - Home</title>
        <?php echo "<link rel='stylesheet' type='text/css' href='".$page_css."'>"?>
        <?php echo "<link rel='stylesheet' type='text/css' href='".$home_css."'>"?>   
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
                        <?php 
                            if(empty($_SESSION['username'])) {
                                echo "<li><a href='fuelwatchapp.php?action=login' title='Login'>Login</a></li>";
                            }  
                        ?>
                        <?php 
                            if(!empty($_SESSION['username'])) {
                                echo "<li><a href='fuelwatchapp.php?action=logout' title='Logout'>Logout</a></li>";
                            }  
                        ?>
                        <?php 
                            if(empty($_SESSION['username'])) {
                                echo "<li><a href='fuelwatchapp.php?action=register' title='Register'>Register</a></li>";
                            }  
                        ?>
                        <?php 
                            if(!empty($_SESSION['username'])) {
                                echo "<li><a href='fuelwatchapp.php?action=profile' title='Profile'>".$_SESSION['username']."</a></li>";
                            }  
                        ?>
                </ul>
        </nav>
        <main id="mainContent" title="mainContent">
            <div id="centersearch">
                <section id="fuelsearch">
                    <form title="search" action="fuelwatchapp.php" method="POST">
                                <div id="searchfuel">
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
                                                else if($favourite){
                                                        if($fuel_type == $user->postedfuel()) {
                                                            echo "<option value='$fuel_type' selected>" . $fuel_type . "</option>"; 
                                                        }
                                                    else {
                                                        echo "<option value='$fuel_type'>" . $fuel_type . "</option>";
                                                    }
                                                }
                                                else {
                                                    echo "<option value='$fuel_type'>" . $fuel_type . "</option>";
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
                                                else if($favourite){
                                                        if($locality_name == $user->postedlocalityname()) {
                                                            echo "<option value='$locality_name' selected>" . $locality_name . "</option>"; 
                                                        }
                                                    else {
                                                        echo "<option value='$locality_name'>" . $locality_name . "</option>";
                                                    }
                                                }
                                                else {
                                                    echo "<option value='$locality_name'>" . $locality_name . "</option>";
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
                                                else if($favourite){
                                                        if($distance == $user->posteddistance()) {
                                                            echo "<option value='$distance' selected>" . $distance . "</option>"; 
                                                        }
                                                    else {
                                                        echo "<option value='$distance'>" . $distance . "</option>";
                                                    }
                                                }
                                                else {
                                                    echo "<option value='$distance'>" . $distance . "</option>";
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
                        <input type="submit" value="Submit Query" name="home">
                    </form>
                </section>
                <section id="stationdisplay">
                    <p>Result: 
                    <?php 
                        if($cheapest_fuel) {
                            echo "Cheapest fuel in Western Australia for date: ".$user->cheapestdate();
                        }
                        else {
                            echo " ".$user->postedfuel().", ".$user->posteddistance()." km's from ".$user->postedlocalityname().".";
                        }
                    ?></p>
                    <?php
                        $fuel_search_view = $user->fuelsearchview();
                        if(($fuel_search_view->getroot()) == null) {
                            echo "<div id='noresults'><p>NO RESULTS</p></div>";
                        }
                        else {
                            echo "<ul id='searchresults'>";
                            $node = $fuel_search_view->getroot();
                             echo "<li>";
                                    echo "<ul class='searchresultitem'>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Title: </label>";
                                            if($node->getdata()->gettitle() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->gettitle()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Trading Name:</label>";
                                            if($node->getdata()->gettradingname() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->gettradingname()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Location: </label>";
                                            if($node->getdata()->getlocation() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getlocation()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Phone: </label>";
                                            if($node->getdata()->getphone() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getphone()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Latitude: </label>";
                                            if($node->getdata()->getlatitude() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getlatitude()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Longitude: </label>";
                                            if($node->getdata()->getlongitude() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getlongitude()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Address: </label>";
                                            if($node->getdata()->getaddress() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getaddress()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Product: </label>";
                                            if($node->getdata()->getproduct() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getproduct()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Date: </label>";
                                            if($node->getdata()->getdate_() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getdate_()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Price: </label>";
                                            if($node->getdata()->getprice() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getprice()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            $lat = floatval($node->getdata()->getlatitude());
                                            $long = floatval($node->getdata()->getlongitude());
                                            echo "<button type='button'><a target='_blank' href='http://maps.google.com/maps?q=$lat,$long'><span>Show On Map</span></a></button>";
                                        echo "</div>";
                                    echo "</ul>";
                                echo "</li>";
                            while($node->getnextnode() != null) {
                                $node = $node->getnextnode();
                                echo "<li>";
                                    echo "<ul class='searchresultitem'>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Title: </label>";
                                            if($node->getdata()->gettitle() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->gettitle()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Trading Name:</label>";
                                            if($node->getdata()->gettradingname() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->gettradingname()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Location: </label>";
                                            if($node->getdata()->getlocation() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getlocation()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Phone: </label>";
                                            if($node->getdata()->getphone() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getphone()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Latitude: </label>";
                                            if($node->getdata()->getlatitude() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getlatitude()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Longitude: </label>";
                                            if($node->getdata()->getlongitude() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getlongitude()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Address: </label>";
                                            if($node->getdata()->getaddress() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getaddress()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Product: </label>";
                                            if($node->getdata()->getproduct() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getproduct()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Date: </label>";
                                            if($node->getdata()->getdate_() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getdate_()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            echo "<label>Price: </label>";
                                            if($node->getdata()->getprice() == "") {
                                               echo "<li>N/A</li>"; 
                                            }
                                            else {
                                               echo "<li>".$node->getdata()->getprice()."</li>"; 
                                            }
                                        echo "</div>";
                                        echo "<div class='searchresultitemfield'>";
                                            $lat = floatval($node->getdata()->getlatitude());
                                            $long = floatval($node->getdata()->getlongitude());
                                            echo "<button type='button'><a target='_blank' href='http://maps.google.com/maps?q=$lat,$long'><span>Show On Map</span></a></button>";
                                        echo "</div>";
                                    echo "</ul>";
                                echo "</li>";
                            }
                        echo "</ul>";
                        } 
                    ?>
                </section>
            </div>
        </main>
        <footer id="pagefooter">
            <h3>A.A.W</h3>
        </footer>
    </body>
<?php $db = null;?>
</html>
