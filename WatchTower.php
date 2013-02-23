<?php
/*
 * WatchTower Logger - Easier CI logging.
 * 
 * WatchTower is a basic Codeigniter logging library that is not meant to 
 * replace CodeIgniters logging or the server lever error logs. The whole 
 * purpose of WatchTower is to provide an easy means to set up and write to a 
 * number of various log files in a consistent and easy way.
 * 
 * The two main points of WatchTower are:
 * - Writing to log files.
 * - Optionally notifying an administrator if it's a SHTF situation. @todo V0.2
 * 
 * @author James McFall <james@mcfall.geek.nz>
 * @version 0.1
 */
class WatchTower {
    
    # Class Properties
    private $_conf  = null;
    private $_ci    = null;
    
    
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
     * using the current server time in the log. 
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
        
        
        # Now try to create an individual log file for each stream.
        foreach ($this->_conf['streams'] as $stream) {
            $touch = @touch($this->_conf['logDir'] . "/" . $stream . ".log");
            
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
