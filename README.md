<img src="http://www.possible-development.com/img/roducks.png" />

# Requisites:

* Git installed in your machine.
* A Local Server installed in your machine like XAMPP, LAMP, WAMP or your favorite.
* PHP Version >= 7.0.x
* MySQL version >= 5.6.x
* An Apache service running.
* A MySQL service running.

# Installation steps:

1. Open a terminal.
2. Clone project from Github `https://github.com/rgocervantes/roducks.git` into your “workspace” `The “workspace” directory varies depending what local server you had installed`.
3. Move to the project’s directory.

4. Add 2 virtual hosts of your “Domain Name”, One for Front-End and other for Back-End `Admin` in your hosts file `This varies depending of your OS`, for example:
```
127.0.0.1	local.yoursite.test
127.0.0.1	admin.local.yoursite.test
```
**Note** Don’t forget to replace “yoursite.test” by your own “Domain Name”.

5. Configure those virtual hosts in the vhosts file of your local server `This varies depending what local server you installed`.
6. Restart Apache service.

7. Run commands below to make some folders.
```
mkdir -p app/Data/roles
mkdir -p app/Data/uploads
```
8. Give write permissions.
```
chmod -R 777 app/Data
```
9. Run the next command.
```
cp core/Data/Config/* app/Data/roles/
```
10. Run command below to create the local config file.
```
cp config/config.local.inc.sample config/config.local.inc
```
11. Edit file `config/config.local.inc` and change value of `domain_name` by your own “Domain Name” for Development environment.
12. Run command below to create the local environments file.
```
cp config/environments.local.inc.sample config/environments.local.inc
```
13. Create a local MySQL database called `roducks`.
14. Create a User and Password for the database you just created and the previous step.
15. Import sql script located in: `app/Schema/Sql/roducks.sql` into your data base.

16. Run command below to create the local Database file.
```
cp config/database.local.inc.sample config/database.local.inc
```
17. Edit file `config/database.local.inc` and set data of your local Database’s User and Password.

18. Run the command below to create the **Super Admin User**.
```
php roducks user:create --dev <YOUR_EMAIL> <YOUR_PASSWORD> --super-admin
```

19. Run commands below to create static folder
```
mkdir -p public/static

pwd
```
20. Copy the route of your project and replace it by `<PATH>` in order to create a symbolic link
```
ln -s <PATH>/app/Data/uploads <PATH>/public/static
```
21. Type in a Web Browser `http://admin.local.<YOUR_DOMAIN_NAME>` and Log In with your credentials to enter to Admin.
22. Type in a Web Browser `http://local.<YOUR_DOMAIN_NAME>` to see the Front-End.

Congratulations! You just installed `Roducks` successfully.

# Deployment steps:

1. Upload files to your Server in QA `or` Production environment.
2. Run commands below to make some folders.
```
mkdir -p app/Data/roles
mkdir -p app/Data/uploads
```
3. Give write permissions.
```
chmod -R 777 app/Data
```
4. Run the next command.
```
cp core/Data/Config/* app/Data/roles/
```
5. Create a local MySQL database called `roducks`.
6. Create a User and Password for the database you just created and the previous step.
7. Import sql script located in: `app/Schema/Sql/roducks.sql` into your data base.
8. Edit file `config/config.inc` and change value of `domain_name` by your own “Domain Name” for Production environment.
9. Edit file `config/database.inc` and set data of your Database’s User and Password.
10. Run the command below to create the **Super Admin User**.
```
php roducks user:create --pro <YOUR_EMAIL> <YOUR_PASSWORD> --super-admin
```
11. Run commands below to create static folder
```
mkdir -p public/static

pwd
```
12. Copy the route of your project and replace it by `<PATH>` in order to create a symbolic link
```
ln -s <PATH>/app/Data/uploads <PATH>/public/static
```
13. Type in a Web Browser `http://admin.<YOUR_DOMAIN_NAME>` and Log In with your credentials to enter to the Back-End.
14. Type in a Web Browser `http://www.<YOUR_DOMAIN_NAME>` to see the Front-End.

Congratulations! You just deployed `Roducks` successfully.
