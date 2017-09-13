# Roducks Instructions
———————————————————
# Requesities:
———————————————————
* Git installed in your machine.
* A Local Server installed in your machine like XAMPP, LAMP, WAMP or your favorite.
* PHP Version >= 5.5.x
* MySQL version >= 5.6.x
* An Apache service running.
* An MySQL service running.

———————————————————
# Installation steps:
———————————————————
1. - Open a terminal
1.1 - Clone project from Github (https://github.com/rgocervantes/roducks.git) into your “workspace”. (The “workspace” directory varies depending what local server you had installed.)
1.2 - Move to the project’s directory.

*** OPTIONAL ***
1.3 - Create a Github repository. (You must have a Github account)
1.4 - Point the project to your Github repository. (Follow the instructions from Github)
a
2.- Add 2 virtual hosts of your “Domain Name”, One for Front-End and other for Back-End (Admin) in your hosts file (This varies depending of your OS), for example:

127.0.0.1	local.yoursite.dev
127.0.0.1	admin.local.yoursite.dev

Note: Don’t forget to replace “yoursite.dev” by your own “Domain Name”.

2.1 - Configure those virtual hosts in the vhosts file of your local server. (This varies depending what local server you installed.)
2.2 - Restart Apache service.

3.- Give some write permissions:
$ chmod -R 777 app/data

4.- Run the next command:
cp core/data/config/* app/data/storage/json/roles/*

5.- Run command below to create the local config file:
$ cp app/config/config.local.inc.sample app/config/config.local.inc
5.2 - Edit value of domain_name by your own “Domain Name” for Development environment.

6.- Run command below to create the local environments file:
$ cp app/config/environments.local.inc.sample app/config/environments.local.inc

7.- Create a local MySQL database called “roducks”
7.1 - Create a User and Password for the database you just created and the previous step.
7.2 - Import sql script located in: “core/data/install/roducks.sql” into your data base.

8.- Run command below to create the local Database file:
$ cp app/config/database.local.inc.sample app/config/database.local.inc
8.1 - Edit data with your local Database’s User and Password

9.- Run the command below to create the Super Admin User:
$ time php core/scripts/cli.php script=users env=dev email=<REPLACE_BY_YOUR_EMAIL> password=<REPLACE_BY_YOUR_PASSWORD>

10.- Type in a Web Browser -> http://admin.local.<YOUR_DOMAIN_NAME>
10.1 - Log In with your credentials to enter to Admin.
10.2 - Type in a Web Browser -> http://local.<YOUR_DOMAIN_NAME> to see the Front-End.

Congratulations! You just installed “Roducks” successfully.


———————————————————
# Deployment steps:
———————————————————
1.- Upload files to your Server in Production environment.

2.- Give some write permissions:
$ chmod -R 777 app/data

3.- Run the next commands:
cp core/data/config/* app/data/storage/json/roles/*

4.- Create a local MySQL database called “roducks”
4.1 - Create a User and Password for the database you just created and the previous step.
4.2 - Import sql script located in: “core/data/install/roducks.sql” into your data base.

5.- Edit file: app/config/config.inc Change value of domain_name by your own “Domain Name” for Production environment.
5.1 - Edit file app/config/database.inc with the database’s data of Production environment.

6.- Run the command below to create the Super Admin User:
$ time php core/scripts/cli.php script=users env=pro email=<REPLACE_BY_YOUR_EMAIL> password=<REPLACE_BY_YOUR_PASSWORD>

7.- Type in a Web Browser -> http://admin.<YOUR_DOMAIN_NAME>
7.1 - Log In with your credentials to enter to the Back-End.
7.2 - Type in a Web Browser -> http://www.<YOUR_DOMAIN_NAME> to see the Front-End.

Congratulations! You just deployed “Roducks” successfully.

