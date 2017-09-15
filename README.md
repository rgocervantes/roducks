# Roducks Instructions

# Requisites:

* Git installed in your machine.
* A Local Server installed in your machine like XAMPP, LAMP, WAMP or your favorite.
* PHP Version >= 5.5.x
* MySQL version >= 5.6.x
* An Apache service running.
* An MySQL service running.

# Installation steps:

1. Open a terminal.
2. Clone project from Github `https://github.com/rgocervantes/roducks.git` into your “workspace” `The “workspace” directory varies depending what local server you had installed`.
3. Move to the project’s directory.

4. Add 2 virtual hosts of your “Domain Name”, One for Front-End and other for Back-End `Admin` in your hosts file `This varies depending of your OS`, for example:
```
127.0.0.1	local.yoursite.dev
127.0.0.1	admin.local.yoursite.dev
```
**Note** Don’t forget to replace “yoursite.dev” by your own “Domain Name”.

5. Configure those virtual hosts in the vhosts file of your local server `This varies depending what local server you installed`.
6. Restart Apache service.

7. Run command below to make `data` folder.
```
mkdir app/data/storage/json/roles
```
8. Give some write permissions.
```
chmod -R 777 app/data
```
9. Run the next command.
```
cp core/data/config/* app/data/storage/json/roles/
```
10. Run command below to create the local config file.
```
cp app/config/config.local.inc.sample app/config/config.local.inc
```
11. Edit file `app/config/config.local.inc` and change value of `domain_name` by your own “Domain Name” for Development environment.
12. Run command below to create the local environments file.
```
cp app/config/environments.local.inc.sample app/config/environments.local.inc
```
13. Create a local MySQL database called `roducks`.
14. Create a User and Password for the database you just created and the previous step.
15. Import sql script located in: `core/data/install/roducks.sql` into your data base.

16. Run command below to create the local Database file.
```
cp app/config/database.local.inc.sample app/config/database.local.inc
```
17. Edit file `app/config/database.local.inc` and set data of your local Database’s User and Password.

18. Run the command below to create the **Super Admin User**.
```
time php core/scripts/cli.php script=users env=dev email=<REPLACE_BY_YOUR_EMAIL> password=<REPLACE_BY_YOUR_PASSWORD>
```
19. Type in a Web Browser `http://admin.local.<YOUR_DOMAIN_NAME>` and Log In with your credentials to enter to Admin.
20. Type in a Web Browser `http://local.<YOUR_DOMAIN_NAME>` to see the Front-End.

Congratulations! You just installed `Roducks` successfully.

# Deployment steps:

1. Upload files to your Server in QA `or` Production environment.
2. Run command below to make `data` folder.
```
mkdir app/data/storage/json/roles
```
3. Give some write permissions.
```
chmod -R 777 app/data
```
4. Run the next command.
```
cp core/data/config/* app/data/storage/json/roles/
```
5. Create a local MySQL database called `roducks`.
6. Create a User and Password for the database you just created and the previous step.
7. Import sql script located in: `core/data/install/roducks.sql` into your data base.
8. Edit file `app/config/config.inc` and change value of `domain_name` by your own “Domain Name” for Production environment.
9. Edit file `app/config/database.inc` and set data of your Database’s User and Password.
10. Run the command below to create the **Super Admin User**.
```
time php core/scripts/cli.php script=users env=pro email=<REPLACE_BY_YOUR_EMAIL> password=<REPLACE_BY_YOUR_PASSWORD>
```
11. Type in a Web Browser `http://admin.<YOUR_DOMAIN_NAME>` and Log In with your credentials to enter to the Back-End.
12. Type in a Web Browser `http://www.<YOUR_DOMAIN_NAME>` to see the Front-End.

Congratulations! You just deployed `Roducks` successfully.

