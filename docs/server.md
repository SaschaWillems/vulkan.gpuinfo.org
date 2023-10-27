# Server documentation

## Cronjob for format listing updates

Getting global format support is slow due to how the database handles bitwise fields (which is how Vulkan stores format info). So unlike other global listings, the format listings are not generated at runtime but rather by cronjobs that run the `..\cronjobs\updateformatlistings.php` script. That script is very heavy on the database and to avoid time outs needs to be run with multiple arguments in multiple cronjobs:

* SERVER_NAME/vulkan.gpuinfo.org/cronjobs/updateformatlistings.php
* SERVER_NAME/cronjobs/updateformatlistings.php?apiversion=1.1
* SERVER_NAME/cronjobs/updateformatlistings.php?apiversion=1.2
* SERVER_NAME/cronjobs/updateformatlistings.php?apiversion=1.3

