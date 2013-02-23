WatchTower-CI-Logging
=====================

WatchTower is a CodeIgniter library for writing to (and automatically generating) a series of log files with the option of notifying an admin when the SHTF (notifications in next version). 

Most applications I work on have some very distinct areas of functionality that require logging on their own. I wanted to make this as simple as possible. By configuring a few logging "streams" (ie basically a list of log files) WatchTower will create these log files for you and you can log to each one by just specifying the stream and a message.

Too many applications I come across have very limited logging implemented and it can make diagnosing issues very difficult. Hopefully by making logging easy, it will happen more often.

## Installation
### The Library
Simply copy the _WatchTower.php_ library file to your _application/libraries_ folder. From there you'll want to add it to the autoloader ( _application/config/autoload.php_ ) in the libraries array.

```php
$autoload['libraries'] = array("WatchTower");
```

### Configuration
WatchTower requires an entry in the config file to set up the relevant log files. So copy/paste and edit the below config line into your CodeIgniter config file ( _application/config/config.php_ ).

```php
/**
 * WatchTower configuration
 */
$config['watchtower'] = array(
    "logDir"        => BASEPATH . "../watchtower-logs",
    "whoToNotify"   => array("you@your-domain.com"), # Each email in this array will be notified
    "streams"       => array("registration", "web-service"), # Each of these streams will have a .log file generated for them
    "timeFormat"    => "Y-m-d G:i:s" # You can override the log entry time format if you wish
);
```

## Usage
Once that initial setup is done, logging becomes very very simple.

```php
# Note the first parameter is the logging stream (ie an individual log)
$this->watchtower->log("registration", "Oh no. Steve tried to enrol again. Don't worry, we stopped him!");
```