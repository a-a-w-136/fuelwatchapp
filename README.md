# fuelwatchapp.com
Fuelwatchapp monitors fuel prices in Western Australia. Users can search by Fuel Type, Location and Distance from location. Locations are  selected from the data collected from GNAF(https://data.gov.au/search?q=gnaf). The location data is campared against the fuel price data collected(daily) from https://www.fuelwatch.wa.gov.au/. Users can register, set a favourite, search the database, login, logout and unregister. 

This app does not use a framework for the simple reason that I wanted to try developing without one. The app attempts to follow the MVC pattern. The following describes this process.

All requests are made to the Controller(fuelwatchapp.com/public_html/fuelwatchapp.php). fuelwatchapp.php fetches "views" according to request type(GET/POST) and stored session parameters. Practically a "view" is fetched by use of the include statment and is therefore included into fuelwatchapp.php and fuelwatchapp.php is subsequently returned to the user. The view is structured according to a model/class found in fuelwatchapp.com/functionality/functions_and_classes.php.
