Changes
=======

## 1.4.2 

* new console command: setPassword (depends on neurolit/etherpad-lite-php-client v0.2.0)
* new version of neurolit/etherpad-lite-php-client: 0.2.0

## 1.4.1 

* Travis: using Github API token for Composer downloads
  (corrects Travis threads)
* Composer: setting Symfony dependancies to 2.2.* instead of 2.*

## 1.4.0

* renamed class names
* Etherpad PHP API: local code replaced by neurolit/etherpad-lite-php-client v0.1.0 from Composer

## 1.3.0

* new function: `deletePad(padID)`
* new function: `getLastEdited(padID)`
* initial text of a pad can be set in `app.yml` configuration file

## 1.2.2

* correction of a bug introduced by 1.2.1

## 1.2.1

* password is not displayed anymore in initial text of a pad

## 1.2.0

* Etherpad-Lite API version MUST be >= 1.2.1
* the console interacts with Etherpad-Lite
* new function: `listAllPads()`
* new function: `getText(padID)`
* web profiler added to dev

## 1.1.0

* layout changes
* `public/` directory renamed into `web/`
* files and directories respect [Silex-Skeleton](https://github.com/fabpot/Silex-Skeleton)

## 1.0.0

* Initial release
