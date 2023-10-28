# Server documentation

## Database

For security reasons, configuration files used to connect to the database are **not** included. To connect to a database a file named `database.config.php` needs to be created in the `database` folder that defines the following constants:

* DBHOST - Location of the database (URL or IP)
* DBNAME - Name of the database
* DBUSER - Username for the database connection
* DBPASSWORD - Password for the database connection

Supported database systems: MySQL and MariaDB. Other SQL database systems may work, but probably need adjustments.

For the database structure (excluding some security related tables) see [this file](../database/structure.sql).

## Cronjob for format listing updates

Getting global format support is slow due to how the database handles bitwise fields (which is how Vulkan stores format info). So unlike other global listings, the format listings are not generated at runtime but rather by cronjobs that run the `..\cronjobs\updateformatlistings.php` script. That script is very heavy on the database and to avoid time outs needs to be run with multiple arguments in multiple cronjobs:

* SERVER_NAME/vulkan.gpuinfo.org/cronjobs/updateformatlistings.php
* SERVER_NAME/cronjobs/updateformatlist ings.php?apiversion=1.1
* SERVER_NAME/cronjobs/updateformatlistings.php?apiversion=1.2
* SERVER_NAME/cronjobs/updateformatlistings.php?apiversion=1.3

