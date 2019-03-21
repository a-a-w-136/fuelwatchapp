# fuelwatchapp
Fuelwatchapp monitors fuel prices in Western Australia. Users can search by Fuel Type, Location and Distance from location. Locations are  selected from the data collected from GNAF(https://data.gov.au/search?q=gnaf). The location data is campared against the fuel price data collected(daily) from https://www.fuelwatch.wa.gov.au/. Users can register, set a favourite, search the database, login, logout, unregister and show the location of stations in google maps. 

This app does not use a framework for the simple reason that I wanted to try developing without one. The app attempts to follow the MVC pattern. The following describes this process.

All requests are made to the Controller(fuelwatchapp.com/public_html/fuelwatchapp.php). fuelwatchapp.php fetches "views" according to request type(GET/POST) and stored session parameters. Practically a "view" is fetched by use of the include statment and is therefore included into fuelwatchapp.php and, fuelwatchapp.php is subsequently returned to the user. The view is structured according to a model/class found in fuelwatchapp.com/functionality/functions_and_classes.php.

The following describes the design issues.

This application is a mix of OOD and procedural. The 'controller' is a procedure, initiating a 'session' is a procedure and gathering/inputting GNAF data is a procudure. These procedures do not belong to a particular class. 

In terms of a client request, there is some processing that takes place when a view is 'included' into the controller(fuelwatchapp.php). This processing of the request should be the concern of the controller and not the view.

A better implementation would be to create a 'Controller' class that is instantiated by a 'index.php' script. Moving any processing from the views into the controller class or a 'Request Processing' class. Including the 'session' procedure into the 'User' class, since a User can create a session. Also, instantiation of a 'Database' object should take place when a 'User' object is constructed. This would mean that the code would not have to pass a reference of the instantiated DataBase object to various functions of a instance of a User class.
