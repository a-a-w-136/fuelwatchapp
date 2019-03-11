<?php 
function session($root_domain_config) {
    try {
        $currentCookieParams = session_get_cookie_params();
        $rootDomain = $root_domain_config;
        $lifetime = 180;
        session_set_cookie_params(
                $lifetime,
                $currentCookieParams["path"],
                $rootDomain,
                $currentCookieParams["secure"],
                true
        );
        if (session_status() == PHP_SESSION_NONE) {
        session_start();
        }
        setcookie(session_name(),session_id(),time()+$lifetime);
        if (session_status() == PHP_SESSION_NONE) {
            throw new Exception("Couldn't create Session");
        }
    }
    catch(Exception $e) {
        throw $e;
    }
}
class Database {
        private $sql_log;
        private $log_path;
        private $dbhost;
        private $username;
        private $password;
        private $dbname;
        private $conn;
    
    function __construct($log_path, $dbhost, $username, $password, $dbname) {
        $this->log_path = $log_path;
        $this->dbhost = $dbhost;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        try {
            $this->connect();
        }
        catch (Exception $e) {
            throw $e;
        }
    }
    function __destruct() {
        fclose($this->sql_log);
        $this->conn->close();
    }
    function connect () {
        try {
            $this->sql_log = fopen($this->log_path, "a");
            if(($this->conn = new mysqli($this->dbhost, $this->username, $this->password, $this->dbname)) == null) {
                throw new Exception("Couldn't Create DB Object\n");
            }
            if(($this->conn->connect_error) != null) {
                $date = date('m/d/Y h:i:s a', time());
                if($this->sql_log) {
                    fwrite($this->sql_log, "\nError : Connection failed:  ".$this->conn->connect_error .$date. "\n");
                }
                throw new Exception("\nError : Connection failed:  ".$this->conn->connect_error .$date. "\n");
            }
            else {
                $date = date('m/d/Y h:i:s a', time());
                if($this->sql_log) {
                    fwrite($this->sql_log, "\nSuccess: Connection made:  ".$this->conn->connect_error .$date. "\n");
                }
            }
        }
        catch (Exception $e){
            throw $e;
        }
    }
    function query($sql_query) {
        try {
            $result;
            if(!($result = $this->conn->query($sql_query))) {
                $date = date('m/d/Y h:i:s a', time());
                if($this->sql_log) {
                    fwrite($this->sql_log, "Error: SQL query - " .$date. " : " .$sql_query.  "\n" );
                }
                throw new Exception("Error: SQL query - " .$date. " : " .$sql_query. "\n");
            }
            else {
                $date = date('m/d/Y h:i:s a', time());
                if($this->sql_log) {
                    fwrite($this->sql_log, "Success : SQL query - " .$date. " : " .$sql_query. "\n" );
                }
            }
            return $result;
        }
        catch (Exception $e){
            throw $e;
        }
    }
}

class User {
//<---------------------Fuel search get-------------------->
    protected $sql_locality_names;
    protected $fuel_types;
    protected $distances;
    protected $locality_names;
    protected $fuel_search_view;
    protected $cheapest_date;
//<---------------------Fuel search post-------------------->   
    protected $verified_input = false;
    protected $verified_locality = false;
    protected $verified_fuel_type = false;
    protected $verified_distance = false;
    protected $locality_name_index = 0;
    protected $locality_pid;
    protected $stations_within_distance_title;
    protected $posted_locality_name;
    protected $posted_fuel;
    protected $posted_distance;
    protected $log;
    
    function homepageget($db, $log) {
        $this->sql_locality_names = "SELECT locality_name, locality_pid FROM wa_locality ORDER BY locality_name";
        $this->fuel_types = array("Unleaded", "Premium Unleaded", "Diesel", "Brand Diesel", "e85", "RON 98", "LPG");
        $this->distances = array("1" , "5", "10", "15", "20", "25", "30", "35","45", "50", "55", "60", "65", "100", "200", "300", "400", "500", "1000");
        $this->locality_names = array();
        $this->fuel_search_view = new LinkedList();
        $this->stations_within_distance_title = new LinkedList();
        $this->log = fopen($log, 'a');
        try {
            $this->querylocalitynames($db);
            $this->getcheapestinWA($db);
        }
        catch (Exception $e) {
            throw $e;
        }
    }
    function homepagepost($db, $log) {
        $this->homepageget($db, $log);
        $this->fuel_search_view = new LinkedList();
        try {
            $this->verifyposteddata($db, $_POST["locality_name"], $_POST["fuel"], $_POST["distance"]);
            $this->getstationsinrange($db);
            $this->dbparametersearch($db);
        }
        catch (Exception $e) {
            throw $e;
        }
    }
    protected function querylocalitynames($db) {
        $result = $db->query($this->sql_locality_names);
        while($row = $result->fetch_assoc()) {
            array_push($this->locality_names, $row['locality_name']);
        }
    }
    function getlocalitynames () {
        return $this->locality_names;
    }
    function getfueltypes () {
        return $this->fuel_types;
    }
    function getdistances () {
        return $this->distances;
    }
     function verifiedinput() {
        return $this->verified_input;
    }
    function verifiedlocality() {
        return $this->verified_locality;
    }
    function verifiedfueltype() {
        return $this->verified_fuel_type;
    }
    function verifieddistance() {
        return $this->verified_distance;
    }
    function postedfuel() {
        return $this->posted_fuel;
    }
    function postedlocalityname() {
        return $this->posted_locality_name;
    }
    function posteddistance() {
        return $this->posted_distance;
    }
    function fuelsearchview() {
        return $this->fuel_search_view;
    }
    function cheapestdate() {
        return $this->cheapest_date;
    }
    function register($db, $log) {
        $this->log = fopen($log, 'a');
        $register_clash_query = "SELECT username FROM users WHERE ";
        $register_query = "INSERT INTO users (username, password, date_created) VALUES ";
        if(isset($_POST['register']) && !empty($_POST['username']) && !empty($_POST['password'])) {
                    if($this->validateusername($_POST['username']) && $this->validatepassword($_POST['password'])) {
                        $register_clash_query .= "username = '".$_POST['username']."'";
                        if($result = $db->query($register_clash_query)){
                            if($row = $result->fetch_assoc()) {
                                $date = date('m/d/Y h:i:s a', time());
                                if($this->log) {
                                    fwrite($this->log, "Username: ".$_POST['username']." is taken ".$date."\n" );
                                }
                                return "Username is taken";
                            }
                            else {
                                $date = date('Y-m-d', time());
                                $register_query .= "('".$_POST['username']."' , '".$_POST['password']."', '".$date."')";
                                if($result = $db->query($register_query)){
                                    $date = date('m/d/Y h:i:s a', time());
                                    if($this->log) {
                                        fwrite($this->log, "Username: ".$_POST['username']." is registered ".$date."\n");
                                    }
                                    $_SESSION['username'] = $_POST['username'];
                                    return "Registered: ".$_POST['username'];
                                }
                                else {
                                   return "1: Error registering: contact admin."; 
                                }
                            }
                        }
                        else {
                            return "2: Error registering: contact admin.";
                        }
                    }
                    else {
                        $date = date('m/d/Y h:i:s a', time());
                        if($this->log) {
                            fwrite($this->log, "Invalid Username:".$_POST['username']." OR Invalid Password: ".$_POST['password']." ".$date."\n" );
                        }
                        return "Invalid username or password.<br>Username: 'a-z, A-Z, 1-9' is allowed<br>Password: 'a-z, A-Z, 1-9, !, @, #, $, %, ^, &, *' is allowed.";
                    }
        }
        else {
            return "Enter username and password";
        }
        return "3: Error registering: contact admin.";
    }
    function login($db, $log) {
        $this->log = fopen($log, 'a');
        $login_query = "SELECT username, password FROM users WHERE ";
        if(isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {            
                    if($this->validateusername($_POST['username']) && $this->validatepassword($_POST['password'])) {
                        $login_query .= "username = '".$_POST['username']."' AND password = '".$_POST['password']."'"; 
                        if($result = $db->query($login_query)) {
                                if($row = $result->fetch_assoc()){
                                    $date = date('m/d/Y h:i:s a', time());
                                    if($this->log) {
                                        fwrite($this->log, "Username:".$_POST['username']." logged in ".$date."\n" );
                                    }
                                    $_SESSION['username'] = $row['username'];
                                    return "Login success";
                                }
                                else {
                                $date = date('m/d/Y h:i:s a', time());
                                if($this->log) {
                                    fwrite($this->log, "Invalid Username:".$_POST['username']." OR Invalid Password: ".$_POST['password']." ".$date."\n");
                                }
                                 return "Invalid username or password.<br>Username: 'a-z, A-Z, 1-9' is allowed<br>Password: 'a-z, A-Z, 1-9, !, @, #, $, %, ^, &, *' is allowed.";
                            }
                        }
                        else {
                            $date = date('m/d/Y h:i:s a', time());
                            if($this->log) {
                                fwrite($this->log, "Unexpected login query (sql)\n" );
                            }
                            return "Error logging in: contact admin.";
                        }
                    }
                    else {
                       return "Password or login format error.";
                    }
        }
        else {
            return "Enter username and password";
        }
        return "Error logging in: contact admin.";
    }
    private function validatepassword($password) {
        $password_as_array = str_split($password);
        if(sizeof($password_as_array) > 100) { return false; }
        foreach($password_as_array as $char) {
            $char = ord($char);
            if(!(($char >= 97 && $char <= 122) || ($char >= 65 && $char <= 90) || ($char >= 48 && $char <= 57) && ($char >= 33 && $char <= 47) )) {
                return false;   
            }
        }
        return true;
    }
    private function validateusername($username) {
        $username_as_array = str_split($username);
        if(sizeof($username_as_array) > 100) { return false; }
        foreach($username_as_array as $char) {
            $char = ord($char);
            if(!(($char >= 97 && $char <= 122) || ($char >= 65 && $char <= 90) || ($char >= 48 && $char <= 57) )) {
                return false;   
            }
        }
        return true;
    }
    protected function verifyposteddata($db, $unverified_locality_name, $unverified_fuel, $unverified_distance) {
        foreach($this->locality_names as $locality_name) {
            if($locality_name == $unverified_locality_name) {
                $this->posted_locality_name = $unverified_locality_name;
                $this->verified_input = true;
                $this->verified_locality = true;
                $sql_pid = "SELECT locality_pid FROM wa_locality WHERE locality_name = '".$locality_name."'";
                if($result = $db->query($sql_pid)) {
                    while($row = $result->fetch_assoc()) {
                        $this->locality_pid = $row['locality_pid'];
                    }
                }
                break;
            }
            else {
                $this->posted_locality_name = $unverified_locality_name;
                $this->verified_input = false;
            }
            $this->locality_name_index++;
        }
        foreach($this->fuel_types as $fuel) {
            if($fuel == $unverified_fuel) {
                $this->posted_fuel = $unverified_fuel;
                $this->verified_input = true;
                $this->verified_fuel_type = true;
                break;
            }
            else {
                $this->posted_fuel = $unverified_fuel;
                $this->verified_input = false;
            }
        }
        foreach($this->distances as $distance) {
            if($distance == $unverified_distance) {
                $this->posted_distance = $unverified_distance;
                $this->verified_input = true;
                $this->verified_distance = true;
                break;
            }
            else {
                $this->posted_distance = $unverified_distance;
                $this->verified_input = false;
            }
        }
    }
    protected function getstationsinrange($db) {
            if($this->verified_input) {
//<-----------Get lat/long of locality---------------->
                $sql_locality_pid = "SELECT latitude, longitude FROM wa_locality_point WHERE locality_pid = '".$this->locality_pid."'";
                $result = $db->query($sql_locality_pid);
                $lat;
                $long;
                if($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $lat = floatval($row['latitude']);
                            $long = floatval($row['longitude']);
                        }
//<--Compare selected lat/long with each station. Store the name(pk) of the station to make next query.------->
                        $sql_station_latlongtitle = "SELECT latitude, longitude, title FROM Stations";
                        $result = $db->query($sql_station_latlongtitle);
                            if ($result->num_rows > 0) {
                                while($stations_row = $result->fetch_assoc()){
                                    $station_lat = floatval($stations_row['latitude']);
                                    $station_long = floatval($stations_row['longitude']);
                                    $dist = $this->distance($lat, $long, $station_lat , $station_long , "K");
                                    if($dist <= floatval($this->posted_distance)) {
                                        $node = new LinkedListNode($stations_row['title']);
                                        $this->stations_within_distance_title->append($node);
                                    }
                                }
                            }
                }
            }
            else {
//<---------Input is not verified. Notify the user--------------->
            } 
        }
//<-----------------https://www.geodatasource.com/developers/php--------------------->
        protected function distance($lat1, $lon1, $lat2, $lon2, $unit) {
          if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
          }
          else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
              return ($miles * 1.609344);
            } else if ($unit == "N") {
              return ($miles * 0.8684);
            } else {
              return $miles;
            }
          }
        }
        protected function dbparametersearch($db) {
            $result;
            $sql_search;
            $date = date('Y-m-d');
            $sql_date_exists = "SELECT date FROM ulp WHERE date = '".$date."'";
            $result = $db->query($sql_date_exists); 
                if($result->num_rows == 0) {
                    $date = date('Y-m-d', time() - (1 * 24 * 60 * 60));
                }
            if($this->verified_input && (($node = $this->stations_within_distance_title->getroot()) != null)) {
                switch ($this->posted_fuel) {
                    case "Unleaded":
                        $sql_search = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, ulp.product, ulp.brand, ulp.date, ulp.price FROM Stations, ulp WHERE ";
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= "(Stations.title = '" .$title. "' AND ulp.title = '" .$title. "' AND ulp.date = '".$date."') ";
                        while($node->getnextnode() != null) {
                        $node = $node->getnextnode();
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= " OR (Stations.title = '" .$title. "' AND ulp.title = '" .$title. "' AND ulp.date = '".$date."')";
                        }
                        $sql_search .= " ORDER BY ulp.price ASC";
                        break;
                    case "Premium Unleaded":
                        $sql_search = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, ulpp.product, ulpp.brand, ulpp.date, ulpp.price FROM Stations, ulpp WHERE ";
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= "(Stations.title = '" .$title. "' AND ulpp.title = '" .$title. "' AND ulpp.date = '".$date."') ";
                        while($node->getnextnode() != null) {
                        $node = $node->getnextnode();
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= " OR (Stations.title = '" .$title. "' AND ulpp.title = '" .$title. "' AND ulpp.date = '".$date."')";
                        }
                        $sql_search .= " ORDER BY ulpp.price ASC";
                        break;
                    case "Diesel":
                        $sql_search = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, diesel.product, diesel.brand, diesel.date, diesel.price FROM Stations, diesel WHERE ";
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= "(Stations.title = '" .$title. "' AND diesel.title = '" .$title. "' AND diesel.date = '".$date."') ";
                        while($node->getnextnode() != null) {
                            $node = $node->getnextnode();
                            $title = $this->justifyquerystring($node->getdata());
                            $sql_search .= " OR (Stations.title = '" .$title. "' AND diesel.title = '" .$title. "' AND diesel.date = '".$date."')";
                        }
                        $sql_search .= " ORDER BY diesel.price ASC";
                    break;
                    case "Brand Diesel":
                        $sql_search = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, dieselbrand.product, dieselbrand.brand, dieselbrand.date, dieselbrand.price FROM Stations, dieselbrand WHERE ";
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= "(Stations.title = '" .$title. "' AND dieselbrand.title = '" .$title. "' AND dieselbrand.date = '".$date."') ";
                        while($node->getnextnode() != null) {
                            $node = $node->getnextnode();
                            $title = $this->justifyquerystring($node->getdata());
                            $sql_search .= " OR (Stations.title = '" .$title. "' AND dieselbrand.title = '" .$title. "' AND dieselbrand.date = '".$date."')";
                        }
                        $sql_search .= " ORDER BY dieselbrand.price ASC";
                    break;
                    case "e85":
                        $sql_search = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, e85.product, e85.brand, e85.date, e85.price FROM Stations, e85 WHERE ";
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= "(Stations.title = '" .$title. "' AND e85.title = '" .$title. "' AND e85.date = '".$date."') ";

                        while($node->getnextnode() != null) {
                            $node = $node->getnextnode();
                            $title = $this->justifyquerystring($node->getdata());
                            $sql_search .= " OR (Stations.title = '" .$title. "' AND e85.title = '" .$title. "' AND e85.date = '".$date."')";
                        }
                        $sql_search .= " ORDER BY e85.price ASC";
                    break;
                    case "RON 98":
                        $sql_search = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, ron98.product, ron98.brand, ron98.date, ron98.price FROM Stations, ron98 WHERE ";
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= "(Stations.title = '" .$title. "' AND ron98.title = '" .$title. "' AND ron98.date = '".$date."') ";
                        while($node->getnextnode() != null) {
                            $node = $node->getnextnode();
                            $title = $this->justifyquerystring($node->getdata());
                            $sql_search .= " OR (Stations.title = '" .$title. "' AND ron98.title = '" .$title. "' AND ron98.date = '".$date."')";
                        }
                        $sql_search .= " ORDER BY ron98.price ASC";
                        break;
                    case "LPG":
                        $sql_search = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, lpg.product, lpg.brand, lpg.date, lpg.price FROM Stations, lpg WHERE ";
                        $title = $this->justifyquerystring($node->getdata());
                        $sql_search .= "(Stations.title = '" .$title. "' AND lpg.title = '" .$title. "' AND lpg.date = '".$date."') ";
                        while($node->getnextnode() != null) {
                            $node = $node->getnextnode();
                            $title = $this->justifyquerystring($node->getdata());
                            $sql_search .= " OR (Stations.title = '" .$title. "' AND lpg.title = '" .$title. "' AND lpg.date = '".$date."')";
                        }
                        $sql_search .= " ORDER BY lpg.price ASC";
                        break;
                }
                if($result = $db->query($sql_search)) {
                    while($row = $result->fetch_assoc()) {
                    $search_item = new SearchItem;
                    $search_item->puttitle($row['title']);
                    $search_item->puttradingname($row['trading_name']);
                    $search_item->putlocation($row['location']);
                    $search_item->putphone($row['phone']);
                    $search_item->putlatitude($row['latitude']);
                    $search_item->putlongitude($row['longitude']);
                    $search_item->putaddress($row['address']);
                    $search_item->putproduct($row['product']);
                    $search_item->putdate_($row['date']);
                    $search_item->putprice($row['price']);
                    $node = new LinkedListNode($search_item);
                    $this->fuel_search_view->append($node);
                    }
                }
                else {
                }
            }
        }
        private function justifyquerystring ($query_string) {
            $split_string = str_split($query_string);
            $justified_string = ""; 
            for($x = 0; $x < sizeof($split_string); $x++) {
                if($split_string[$x] == "'") {
                    $justified_string .= "'";
                }
                $justified_string .= $split_string[$x]; 
            }
        return $justified_string;
        }
        protected function getcheapestinWA($db) {
            $search_item;
            $date = date('Y-m-d');
            $this->cheapest_date = $date;
            $sql_date_exists = "SELECT date FROM ulp WHERE date = '".$date."'";
            $result = $db->query($sql_date_exists); 
                if($result->num_rows == 0) {
                    $date = date('Y-m-d', time() - (1 * 24 * 60 * 60));
                    $this->cheapest_date = $date;
                }
            $sql_cheapest_ulp = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, ulp.product, ulp.brand, ulp.date, ulp.price FROM Stations, ulp WHERE Stations.title = ulp.title AND ulp.date = '".$date."' AND ulp.price = (SELECT MIN(price) FROM ulp WHERE date = '".$date."') ";
            $result = $db->query($sql_cheapest_ulp);
                while($row = $result->fetch_assoc()) {
                $search_item = new SearchItem;
                $search_item->puttitle($row['title']);
                $search_item->puttradingname($row['trading_name']);
                $search_item->putlocation($row['location']);
                $search_item->putphone($row['phone']);
                $search_item->putlatitude($row['latitude']);
                $search_item->putlongitude($row['longitude']);
                $search_item->putaddress($row['address']);
                $search_item->putproduct($row['product']);
                $search_item->putdate_($row['date']);
                $search_item->putprice($row['price']);
                $node = new LinkedListNode($search_item);
                $this->fuel_search_view->append($node);
                }
            $sql_cheapest_ulpp = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, ulpp.product, ulpp.brand, ulpp.date, ulpp.price FROM Stations, ulpp WHERE Stations.title = ulpp.title AND ulpp.date = '".$date."' AND ulpp.price = (SELECT MIN(price) FROM ulpp WHERE date = '".$date."') ";
            $result = $db->query($sql_cheapest_ulpp);
                while($row = $result->fetch_assoc()) {
                $search_item = new SearchItem;
                $search_item->puttitle($row['title']);
                $search_item->puttradingname($row['trading_name']);
                $search_item->putlocation($row['location']);
                $search_item->putphone($row['phone']);
                $search_item->putlatitude($row['latitude']);
                $search_item->putlongitude($row['longitude']);
                $search_item->putaddress($row['address']);
                $search_item->putproduct($row['product']);
                $search_item->putdate_($row['date']);
                $search_item->putprice($row['price']);
                $node = new LinkedListNode($search_item);
                $this->fuel_search_view->append($node);
                }
            $sql_cheapest_diesel = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, diesel.product, diesel.brand, diesel.date, diesel.price FROM Stations, diesel WHERE Stations.title = diesel.title AND diesel.date = '".$date."' AND diesel.price = (SELECT MIN(price) FROM diesel WHERE date = '".$date."') ";
            $result = $db->query($sql_cheapest_diesel);
                while($row = $result->fetch_assoc()) {
                $search_item = new SearchItem;
                $search_item->puttitle($row['title']);
                $search_item->puttradingname($row['trading_name']);
                $search_item->putlocation($row['location']);
                $search_item->putphone($row['phone']);
                $search_item->putlatitude($row['latitude']);
                $search_item->putlongitude($row['longitude']);
                $search_item->putaddress($row['address']);
                $search_item->putproduct($row['product']);
                $search_item->putdate_($row['date']);
                $search_item->putprice($row['price']);
                $node = new LinkedListNode($search_item);
                $this->fuel_search_view->append($node);
                }
            $sql_cheapest_dieselbrand = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, dieselbrand.product, dieselbrand.brand, dieselbrand.date, dieselbrand.price FROM Stations, dieselbrand WHERE Stations.title = dieselbrand.title AND  dieselbrand.date = '".$date."' AND dieselbrand.price = (SELECT MIN(price) FROM dieselbrand  WHERE date = '".$date."') ";
            $result = $db->query($sql_cheapest_dieselbrand);
                while($row = $result->fetch_assoc()) {
                $search_item = new SearchItem;
                $search_item->puttitle($row['title']);
                $search_item->puttradingname($row['trading_name']);
                $search_item->putlocation($row['location']);
                $search_item->putphone($row['phone']);
                $search_item->putlatitude($row['latitude']);
                $search_item->putlongitude($row['longitude']);
                $search_item->putaddress($row['address']);
                $search_item->putproduct($row['product']);
                $search_item->putdate_($row['date']);
                $search_item->putprice($row['price']);
                $node = new LinkedListNode($search_item);
                $this->fuel_search_view->append($node);
                }
            $sql_cheapest_e85 = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, e85.product, e85.brand, e85.date, e85.price FROM Stations, e85 WHERE Stations.title = e85.title AND e85.date = '".$date."' AND e85.price = (SELECT MIN(price) FROM e85  WHERE date = '".$date."') ";
            $result = $db->query($sql_cheapest_e85);
                while($row = $result->fetch_assoc()) {
                $search_item = new SearchItem;
                $search_item->puttitle($row['title']);
                $search_item->puttradingname($row['trading_name']);
                $search_item->putlocation($row['location']);
                $search_item->putphone($row['phone']);
                $search_item->putlatitude($row['latitude']);
                $search_item->putlongitude($row['longitude']);
                $search_item->putaddress($row['address']);
                $search_item->putproduct($row['product']);
                $search_item->putdate_($row['date']);
                $search_item->putprice($row['price']);
                $node = new LinkedListNode($search_item);
                $this->fuel_search_view->append($node);
                }
            $sql_cheapest_ron98 = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, ron98.product, ron98.brand, ron98.date, ron98.price FROM Stations, ron98 WHERE Stations.title = ron98.title AND ron98.date = '".$date."' AND ron98.price = (SELECT MIN(price) FROM ron98  WHERE date = '".$date."') ";
            $result = $db->query($sql_cheapest_ron98);
                while($row = $result->fetch_assoc()) {
                $search_item = new SearchItem;
                $search_item->puttitle($row['title']);
                $search_item->puttradingname($row['trading_name']);
                $search_item->putlocation($row['location']);
                $search_item->putphone($row['phone']);
                $search_item->putlatitude($row['latitude']);
                $search_item->putlongitude($row['longitude']);
                $search_item->putaddress($row['address']);
                $search_item->putproduct($row['product']);
                $search_item->putdate_($row['date']);
                $search_item->putprice($row['price']);
                $node = new LinkedListNode($search_item);
                $this->fuel_search_view->append($node);
                }
            $sql_cheapest_lpg = "SELECT Stations.title, Stations.trading_name, Stations.location, Stations.phone, Stations.latitude, Stations.longitude, Stations.address, lpg.product, lpg.brand, lpg.date, lpg.price FROM Stations, lpg WHERE Stations.title = lpg.title AND lpg.date = '".$date."' AND lpg.price = (SELECT MIN(price) FROM lpg  WHERE date = '".$date."') ";
            $result = $db->query($sql_cheapest_lpg);
                while($row = $result->fetch_assoc()) {
                $search_item = new SearchItem;
                $search_item->puttitle($row['title']);
                $search_item->puttradingname($row['trading_name']);
                $search_item->putlocation($row['location']);
                $search_item->putphone($row['phone']);
                $search_item->putlatitude($row['latitude']);
                $search_item->putlongitude($row['longitude']);
                $search_item->putaddress($row['address']);
                $search_item->putproduct($row['product']);
                $search_item->putdate_($row['date']);
                $search_item->putprice($row['price']);
                $node = new LinkedListNode($search_item);
                $this->fuel_search_view->append($node);
                }
        }
}
class Registereduser extends User {
    
    private $favourite = false;
    
    function profilepost($db, $log) {
        $this->homepagepost($db, $log);
        try {
            $this->verifyposteddata($db, $_POST["locality_name"], $_POST["fuel"], $_POST["distance"]);
            return $this->createfavourite($db);
        }
        catch (Exception $e) {
            throw $e;
        }
        
    }
    function profileget($db, $log) {
        $this->homepageget($db, $log);
        try {
            $this->getfavourite($db);
            if($this->favourite) {
                $this->fuel_search_view = new LinkedList();
                $this->homepagefavourite($db);
            }
        }
        catch (Exception $e) {
            throw $e;
        }
        
    }
    function homepagefavourite($db) {
        try {
            $this->getstationsinrange($db);
            $this->dbparametersearch($db); 
        }
        catch (Exception $e) {
            throw $e;
        }
        
    }
    function favourite() {
        return $this->favourite;
    }
    function createfavourite($db) {
        if($this->verified_input) {
            $sql_exists_fav = "SELECT username FROM favourite WHERE username = '".$_SESSION['username']."'";
            if($result = $db->query($sql_exists_fav)) {
                if($row = $result->fetch_assoc()) {
                    $sql_delete_favourite = "DELETE FROM favourite WHERE username = '".$_SESSION['username']."'";
                    if(!($result = $db->query($sql_delete_favourite))) {
                         return "2:Error Creating Favourite";
                    }
                    $sql_insert_favourite = "INSERT INTO favourite (username, locality_name, distance, product) VALUES ('".$_SESSION['username']."', '".$_POST['locality_name']."', '".$_POST['distance']."', '".$_POST['fuel']."')";
                    if($result = $db->query($sql_insert_favourite)) {
                        return "Favourite Saved";
                    }
                    else {
                        return "3:Error Creating Favourite";
                    }
                }
                else {
                    $sql_insert_favourite = "INSERT INTO favourite (username, locality_name, distance, product) VALUES ('".$_SESSION['username']."', '".$_POST['locality_name']."', '".$_POST['distance']."', '".$_POST['fuel']."')";
                    if($result = $db->query($sql_insert_favourite)) {
                        return "Favourite Saved";
                    }
                    return "1:Error Creating Favourite";
                }
            }
            else {
                return "4:Error Creating Favourite";
            }
        }
        return "5:Error Creating Favourite";
        
    }
    function getfavourite($db) {
        $sql_favourite = "SELECT username, locality_name, distance, product FROM favourite WHERE username = '".$_SESSION['username']."'";
        $result = $db->query($sql_favourite);
        if($row = $result->fetch_assoc()) {
                $this->verifyposteddata($db, $row['locality_name'], $row['product'], $row['distance']);
                $this->favourite = true;
        }
    }
    function deleteaccount($db, $log) {
        $sql_delete_account = "DELETE FROM users WHERE username = '".$_SESSION['username']."'";
        $sql_delete_favourite = "DELETE FROM favourite WHERE username = '".$_SESSION['username']."'";
        $profile_log = fopen($log, 'a');
        if($result = $db->query($sql_delete_account)) {
            $_SESSION['username'] = null;
            if($profile_log) {
                fwrite($profile_log, $sql_delete_account. "\n");
            }
        }    
    }
}
class LinkedList {
    private $root;
    private $cur;
    
    function __construct() {
        $this->root = null;
    }
    function getroot() {
        return $this->root;
    }
    
    function append($node) {
        if($this->root == null) {
            $this->root = $node;
        }
        else {
            $this->cur = $this->root;
            while(1) {
                if($this->cur->getnextnode() == null) {
                    $this->cur->setnextnode($node);
                    break;
                }
                else {
                    $this->cur = $this->cur->getnextnode();
                }
            }
        }
    }
}
class LinkedListNode {
    private $data = null;
    private $next_node = null;
    
    function __construct($data) {
        $this->data = $data;
    }
    function getnextnode() {
        return $this->next_node;
    }
    function setnextnode($node) {
        $this->next_node = $node;
    }
    function getdata() {
        return $this->data;
    }
}
class SearchItem {
    private $title;
    private $trading_name;
    private $location;
    private $phone;
    private $latitude;
    private $longitude;
    private $address;
    private $product;
    private $date_;
    private $price;

    function gettitle() {
        return $this->title;
    }
    function gettradingname() {
        return $this->trading_name;
    }
    function getlocation() {
        return $this->location;
    }
    function getphone() {
        return $this->phone;
    }
    function getlatitude() {
        return $this->latitude;
    }
     function getlongitude() {
        return $this->longitude;
    }
    function getaddress() {
        return $this->address;
    }
    function getproduct() {
        return $this->product;
    }
    function getdate_() {
        return $this->date_;
    }
    function getprice() {
        return $this->price;
    }
    function puttitle($title) {
        $this->title = $title;
    }
    function puttradingname($trading_name) {
        $this->trading_name = $trading_name;
    }
    function putlocation($location) {
        $this->location = $location;
    }
    function putphone($phone) {
        $this->phone = $phone;
    }
    function putlatitude($latitude) {
        $this->latitude = $latitude;
    }
     function putlongitude($longitude) {
        $this->longitude = $longitude;
    }
    function putaddress($address) {
        $this->address = $address;
    }
    function putproduct($product) {
        $this->product = $product;
    }
    function putdate_($price) {
        $this->date_ = $price;
    }
    function putprice($price) {
        $this->price = $price;
    } 
}
?>