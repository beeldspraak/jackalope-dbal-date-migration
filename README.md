Migration script
================
At some point the format, at which Jackalope Doctrine DBAL saved dates, changed from 'r' (Thu, 21 Dec 2000 16:01:07 +0200) to 'Y-m-d H:i:s'
This scripts helps to update your database and resaves every node that has a Date property to save it in the new format.

Installation
------------

The easiest way is to just clone the repository and install with composer

    composer install

Next, configure your database. Copy cli-config.php.dist to cli-config.php and fill in your database credentials.

Last step is to run command

    bin/migrate jackalope:dbal:migrate-date-properties

Or, if you want to see what would be updated, run the dry-run

    bin/migrate jackalope:dbal:migrate-date-properties --dry-run