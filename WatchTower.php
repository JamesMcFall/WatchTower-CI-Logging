<?php
/*
 * WatchTower Logger - Easier CI logging.
 * 
 * WatchTower is a basic Codeigniter logging library that is not meant to 
 * replace CodeIgniters logging or the server level error logs. 
 * 
 * The whole purpose of WatchTower is to provide an easy means to set up and 
 * write to a number of various log files in a consistent and easy way.
 * 
 * The two main points of WatchTower are:
 * - Writing to log files.
 * - Optionally notifying a list of email addresses if it's a SHTF situation.
 * 
 * @author James McFall <james@mcfall.geek.nz>
 * @version 0.2.1
 */
class WatchTower {
    
    /**
     * Class Properties
     */
    private $_conf  = null; # Stores the config file watchtower settings
    private $_ci    = null; # Instance of CI
    
    
    /**
     * Constructor
     * 
     * Check the config file for the config entries. Try to set up the log files
     * and structure based on that config.
     * 
     * @return <void>
     */
    public function __construct() {
        
        $this->_ci = &get_instance();
        
        # Load up the CI configuration information & validate it
        $this->_conf = $this->_ci->config->item("watchtower");
        $this->_validateConfig();
        
        # Build the log dirs and log files if they don't exist
        $this->_buildFileStructure();
    }
    
    
    /**
     * Write message to a log file
     * 
     * This method will write the supplied message to the specified log file 
     * (stream) using the current server time in the log. 
     * 
     * @param <string> $stream - The log stream
     * @param <string> $message - The error message
     * @param <boolean> $notify  - Whether to notify the person specified in 
     *                             the config file.
     * @return <void>
     */
    public function log($stream, $message, $notify = false) {
        
        $time = new DateTime();
        
        # Open the log file for editing (a = append mode).
        $logFilePath = $this->_conf['logDir'] . "/" . $stream . ".log";
        $logFile = new SplFileObject($logFilePath, "a");
        
        # Append to the end of the log file with a time and a message
        $logFile->fwrite(
            $time->format($this->_conf["timeFormat"]) . " - " . 
            $message . "\n\n"
        );
        
        if ($notify === true) {
            $this->_sendNotificationEmail($time, $message);
        }
        
    }
    
    /**
     * Send Notification Email
     * 
     * This method builds a very basic html email to send to the people in the
     * notify array in the config file. Basic fallback to non-html is just the
     * log line entry.
     * 
     * @param <DateTime> $time
     * @param <string> $message
     * @return <boolean> 
     */
    private function _sendNotificationEmail($time, $message) {
        
        # If the email library is not loaded, load it up
        if (!isset($this->_ci->email)) {
            $this->_ci->load->library('email');
        }
        
        $timeString = $time->format($this->_conf["timeFormat"]);
        
        # Build the basic HTML message for the email
        $htmlMessage  = "<h3>WatchTower Notification: " . $_SERVER['HTTP_HOST'] . "</h3>";
        $htmlMessage .= "<b>Time:</b> " . $timeString . "<br /><br />";
        $htmlMessage .= "<b>Message:</b> " . $message;
        
        # Not sure if this is required, but set the mailtype to HTML
        $this->_ci->email->initialize(array('mailtype' => 'html'));

        # Set up message details
        $this->_ci->email->from("watchtower@" . $_SERVER['HTTP_HOST'], "WatchTower Logger");
        $this->_ci->email->reply_to("watchtower@" . $_SERVER['HTTP_HOST']);
        $this->_ci->email->subject("WatchTower Notification: " . $_SERVER['HTTP_HOST']);
        $this->_ci->email->message($htmlMessage);
        $this->_ci->email->set_alt_message($timeString . " - " . $message);
        
        # Add all of the "who to notify" entries to the email
        $count = 0;
        foreach ($this->_conf["whoToNotify"] as $emailAddress) {
           
            # First email address set to "to", others are cc'd.
            if ($count === 0) {
                $this->_ci->email->to($emailAddress);
            } else {
                $this->_ci->email->cc($emailAddress);
            }
            
            $i++;
        }
        
        return $this->_ci->email->send();
    }
    
    
    /**
     * Build the log dir and files.
     * 
     * This method tries to set up the log directory from the config file and an
     * individual config file for each of the logging streams specified in the
     * config. If it fails it throwns an exception and these actions have to be
     * manually completed.
     * 
     * @return <void>
     */
    private function _buildFileStructure() {
        
        # Log Directory Creation
        # Try to make the log directory if it doesn't exist
        if (!is_dir($this->_conf['logDir'])) {
            
            # Lets see if we can make a log directory
            $mkdir = @mkdir($this->_conf['logDir']);
            
            if (!$mkdir) {
                throw new Exception("WatchTower Exception: Failed to create log
                    directory at " . $this->_conf['logDir'] . ". Please
                    manually create and set permissions to 777");
            }
            
        }
        
        # Log File Creation
        # Try to create an individual log file for each stream if they don't exist.
        foreach ($this->_conf['streams'] as $stream) {
            $filePath = $this->_conf['logDir'] . "/" . $stream . ".log";
            
            # Skip if this file already exists
            if (file_exists($filePath)) {
                continue;
            }
            
            # Try to create the log file.
            $touch = @touch($filePath);
            
            if (!$touch) {
                throw new Exception("WatchTower Exception: Failed to create log
                    files for each stream. Please set permissions for the log
                    directory (" . $this->_conf['logDir'] . ") to 777 and try again.");
            }
        }
        
        
    }
    
    
    /**
     * Validate The Cofig File Entry
     * 
     * This method checks that the config file has all the required information
     * set up.
     * 
     * @return <void>
     */
    private function _validateConfig() {
        
        if (!$this->_conf) {
            throw new Exception("WatchTower Exception: No WatchTower 
                configuration located in config file. Please see documentation.");
        }
        
        if (!is_array($this->_conf['streams'])) {
            throw new Exception("WatchTower Exception: No streams specified in
                configuration file. Please refer to documentation");
        }
        
        if (!is_array($this->_conf['whoToNotify'])) {
            throw new Exception("WatchTower Exception: No one to notify 
                specified in configuration file. Please refer to documentation");
        }
        
    }
}

?>