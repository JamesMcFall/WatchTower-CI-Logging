WatchTower-CI-Logging
=====================

WatchTower is a CodeIgniter library for writing to (and automatically generating) a series of log files with the option of notifying an admin when the SHTF. 

Most applications I work on have some very distinct areas of functionality that require logging on their own. I wanted to make this as simple as possible. By configuring a few logging "streams" (ie basically a list of log files) WatchTower will create these log files for you and you can log to each one by just specifying the stream and a message. If your application does a large amount of logging, an option is available to have WatchTower automatically create a month or day specific log file instead of one single very large log file.

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
    "timeFormat"    => "Y-m-d G:i:s", # You can override the log entry time format if you wish
    "storageMode"   => "monthly" # Options: "single", "monthly", "daily". WatchTower can split logs into monthly or daily files if you prefer.
);
```

## Usage
Once that initial setup is done, logging becomes very very simple.

### Basic logging
Logging is very straightforward. The first parameter specifies which log/stream you want to write into. The second parameter is the message to put in the log file (automatically prepended with a date/time in the format specified in the config file).
```php
# Note the first parameter is the logging stream (ie an individual log)
$this->watchtower->log("registration", "Oh no. Steve tried to enrol again. Don't worry, we stopped him!");
```

### Sending a notification email out when an error is logged.
If you want to notify the email addresses specified in the config file, supply a third parameter of true.

```php
# Note the third parameter is now "true". This is the "Notify" parameter.
$this->watchtower->log("registration", "Oh no. Steve tried to enrol again. Don't worry, we stopped him!", true);
```

### Dumping debugging data into the log
In some instances you'll likely be wanting to dump an array or object into a log. A method has been added into the logger to make this easy.

```php
$o = new stdClass();
$o->one   = 1;
$o->two   = 2;
$o->three = 3;
            
$this->watchtower->log("registration", "Log an object: " . $this->watchtower->dumpVarToString($o));
```
