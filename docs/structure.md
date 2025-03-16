# Vulkan Hardware structure overview

## Basics

The project mostly uses PHP 8.x (no external libraries or frameworks) with a bit of JavaScript. MySQL is used for the database.

## Libraries

The following Javascript are used in this project. They need to be put in the [external](../external/) folder, and are not included in the repository. See [header.php](../includes/header.php) for how they are included

* Bootstrap v3.x
* jQuery 2.x
* ApexCharts v3.x
* Datatables with Bootstrap 3 integration and the following addons
    * Yet Another DataTables Column Filter (yadcf)
    * Responsive
    * FixedHeader
* Glyphicons font

## Folder structure

| Folder        | Description      |
| ------------- | ------------- |
| api | Contains api routes used by the database (internal), the client application (v1, v2, etc.) and public apis (also v1, v2, etc.) |
| cronjobs | Some of the data is so complex that it can't be fetched at runtime, so instead cronjobs are used to generate static files (e.g. for format support) |
| database | Files related to the database connection and configuration | 
| external | Contains above mentioned JavaScript libraries and external css files |
| includes | Assorted include files used in other PHP files |
| images | Image files used for the frontend |
| js | Contains JavaScript used by the frontend |
| profiles | Contains schemas for the [Vulkan profile report export](profiles.md) |
| reportcompare | Files used for displaying multiple reports |
| reportdisplay | Files used to display a single report |
| services | Assorted api services (outdated) |
| tools | PHP scripts for [maintenance](maintenance.md) |
| root | All global listings of the database and several files used throughout other pages like a page builder class |
