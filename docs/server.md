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

## Cronjob for extension statistics

Similarly, real-time database queries on extension coverage statistics has become too slow due to the large amount of data. Instead extension statitics are aggregated using a a cronjob that runs the ``..\cronjobs\updateextensionlistings.php` script. This fills the `extension_stats` table that is used for the extension listing then.

## Caching

The database implements a simple caching mechanism for reports. First time a report is displayed, the generated output (HTML) will be stored to disk and is loaded for consecutive displays. If a report is updated, the cache will be invalidated. This decreases the number of database queries. If anything changes about how reports are display, the cache should be deleted (remove all files in the reportcache folder).