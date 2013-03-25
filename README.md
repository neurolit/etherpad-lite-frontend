Etherpad-Lite frontend
======================

[![Build Status](https://travis-ci.org/neurolit/etherpad-lite-frontend.png?branch=master)](https://travis-ci.org/neurolit/etherpad-lite-frontend)

## First install

1. In the root directory, run:

		$ curl -s http://getcomposer.org/installer | php -- --install-dir=bin
	
2. At first, in order to populate vendor directory with third-party bundles, run:

		$ php bin/composer.phar install

## Maintenance

Each time you modify composer.json, or each time you want to update
vendor directory, run:

	$ php bin/composer.phar update

## Testing

In order to run the test suite, you need to install phpunit:

	$ php bin/composer.phar install --dev

Afterwards you can run the test suite:

	$ php vendor/phpunit/phpunit/phpunit.php
