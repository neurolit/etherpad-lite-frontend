Etherpad-Lite frontend
======================

[![Build Status](https://travis-ci.org/neurolit/etherpad-lite-frontend.png?branch=master)](https://travis-ci.org/neurolit/etherpad-lite-frontend)

## What is it?

This application, based on [Silex framework](http://silex.sensiolabs.org/), is a web and console frontend to [Etherpad-Lite](http://etherpad.org/).

* Web users can create **public or password protected** pads. They can add a suffix to the pad name, its first part being generated.
* Console admins can list all pads, delete some of them, get the content of a pad, etc.

## First install

### Prerequisites

You MUST use Etherpad-Lite >= 1.2.1.

### Install Composer

1. In the root directory, run:

		$ curl -s http://getcomposer.org/installer | php -- --install-dir=bin
	
2. At first, in order to populate vendor directory with third-party modules, run:

		$ php bin/composer.phar install

### Set permissions

`cache/` and `logs/` directories should be writable by *Apache* (replace `www-data` by *Apache* process `uid`):

	$ chown www-data:www-data cache/ logs/

### Configure MySQL and Etherpad-Lite connections

Copy `config/app_default.yml` into `config/app.yml` and modify it.

	$ cp config/app_default.yml config/app.yml

Here are the statements for creating the MySQL database:

	CREATE DATABASE your_database_name DEFAULT CHARACTER SET 'utf8' DEFAULT COLLATE 'utf8_bin' ;
	CREATE TABLE pad_creation (pad_id VARCHAR(250) NOT NULL PRIMARY KEY, timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, creator_address VARCHAR(15), creator_inria_login char(8) DEFAULT NULL) ;

### Modifying the text on the webpages

You can modify the text of the webpages by creating `config/locales/en.yml` and `config/locales/fr.yml`. Feel free to use `config/locales/en_default.yml` and `config/locales/fr_default.yml` as start points.

### Configuring Apache

Here is a sample for Apache:

	DocumentRoot /APP_DIRECTORY/web
	<Directory "/APP_DIRECTORY/web">
        Options -MultiViews

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </Directory>

## Maintenance

Each time you modify composer.json, or each time you want to update
vendor directory, run:

	$ php bin/composer.phar update

If you modify one of the templates, delete `cache/twig` directory in order to refresh the cache:

	$ rm -rf cache/twig/

## Testing

In order to run the test suite, you need to install phpunit:

	$ php bin/composer.phar install --dev

Afterwards you can run the test suite:

	$ php vendor/phpunit/phpunit/phpunit.php

## Using the console

Some simple EPL actions can be performed via the console:

	$ ./console listAllPads
	$ ./console getText padID
	$ ./console deletePad padID
	$ ./console getLastEdited padID
	$ ./console setPassword protectedPadID newPassword
