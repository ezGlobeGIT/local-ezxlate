<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/*
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_ezxlate;

use DateTime;
use stdClass;

/**
 * Generic class to manage api
 *
 * Contains generic methods to
 *   - analyse parameters,
 *   - check authentification,
 *   - build the answer message,
 *   - launch the specific method called by client
 *
 * This class must be inherited by a specific class for each entry point
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class api {

    /** @var null|stdClass $param Parameters received by API */
    protected $param = null;

    /** @var null|stdClass $data datas to send back */
    protected $data = null;

    /** @var null|stdClass $answer answer to send back */
    protected $answer = null;

    /**
     * Build the answer in case of error 
     * (static method to be used anywhere)
     *
     * @param string $code error code (default "error", other error)
     * @param string $code error message 
     * @return stdClass Returns the object to convert in JSON
     */
    static function failed($code = 'error', $message = '') {
        $answer = new \stdClass();
        $answer->code = $code;
        if ( !empty($message)) $answer->message = $message;
        return $answer;
    }

    /**
     * Constructor
     *
     * @param stdClass $param parmeters sent by the client, in object format (after Json decoding)
     */    
    function __construct($param) {
        $this->param = (object) $param;
    }

    /**
     * Prepare to launch the method corresponding to the client request (and call the launcing method)
     *
     * @return stdClass Returns the final answer
     */
    function process() {
        // Prepare answer and data for answer
        $this->data = new \stdClass();
        $this->answer = new \stdClass();
        $this->answer->code = "ok";
        
        // Check authentification, return eror message if auhentication is not completed
        if ( !empty($message = $this->check_authentification())) return $this->error("auth", $message);
        
        // Check and analyse other parameters
        $answer = $this->check_parameters();
        if (!empty($answer)) return $answer;
        
        // Launch the method to execute
        try {
            $answer = $this->do();
        } catch (\Throwable $e) {
            return $this->error('error', $e->getMessage());
        }
        // The method can build the answer and datas in attributes (answer, data), or directly return the answer
        if (empty($answer)) $answer = $this->answer;
        if (empty($answer->data) and !empty((array) $this->data)) $answer->data = $this->data;
        return $answer;
    }

    /**
     * Check the parameters
     * This method should be overloaded in inherited class
     *
     * @return string|null Returns an error message or null (if parameters are correct)
     */
    protected function check_parameters() {
        return null;
    }

    /**
     * Launch the method depending of the precise request (usualy based on one of the sent parameters) 
     * This method must be overloaded in inherited class
     *
     * @return stdClass|null Returns the final answer, or null if the answer is bulits in attributes (answer, data)
     */
    abstract protected function do();
    
    /**
     * Build the answer in case of error 
     *
     * @param string $code error code (default "error", other error)
     * @param string $code error message 
     * @return stdClass Returns the object to convert in JSON
     */
    function error($code = "error", $message = "") {
        return static::failed($code, $message);
    }
    
    /**
     * Check authentification, from parameters
     *
     * @return string|null Returns the error message, or null when authentication is completed
     */
    protected function check_authentification() {
        // Is API enabled
        if ( get_config("local_ezxlate", "open") != 1 ) return "API disabled";
        // Check key
        if ( empty(get_config("local_ezxlate", "key"))) return "Empty key, API disabled";
        if ( strlen(get_config("local_ezxlate", "key")) < 10 ) return "Key is too short, API disabled";
        if ( empty($this->param->key)) return "Key not provided in the request";
        if ( $this->param->key != get_config("local_ezxlate", "key")) return "Authentification failed";
        // Check client IP 
        if ( $this->iprestricted() ) 
            return "Your IP address " . strtolower(trim($_SERVER["REMOTE_ADDR"])) . " is not allowed";
        // Check user to use for API actions
        $userid = get_config("local_ezxlate", "userid");
        if (empty($userid)) return "No user defined for the API";
        $user = \core_user::get_user($userid);
        if (empty($user) or $user->deleted) return "User defined for the API doesn't exist";
        if ($user->suspended) return "User defined for the API is suspended";
        if (!has_capability('local/ezxlate:use', \context_system::instance(), $userid))
                return "User defined for the API doesn't have the capability to use the API";
        // Set the user as connected user
        \core\session\manager::set_user($user);
        
        return null;
    }
    
    /**
     * Check if client IP is restricted or allowed
     *
     * @return bool True is IP is restricted, false if allowed
     */
    protected function iprestricted() {
        // Get an array of allowed IPs from settings
        $ips = [];
        foreach( explode("\n", str_replace(",", "\n", get_config("local_ezxlate", "ips"))) as $ip) {
            $ip = strtolower(trim($ip));
            if (!empty($ip)) $ips[] = $ip;
        }
        if (empty($ips)) return false;      // No IP in allowed list means no restrictions on IP adresses
        
        // Get client IP
        $myip = strtolower(trim($_SERVER["REMOTE_ADDR"]));
        foreach ($ips as $ip) {
            if ($myip == $ip) return false;    // My IP is allowed
        }
        // My IP is not found in allowed IP list
        return true; 
    }
    
    /**
     * Get version number of the plugin
     *
     * @return string Version number of the plugin
     */
    function version() {
        // Extract the version number of this plugin
        global $CFG;
        $pluginpath = $CFG->dirroot . '/local/ezxlate/version.php';
        if (file_exists($pluginpath)) {
            $plugin = new \stdClass();
            include($pluginpath);
            return $plugin->version;
        } else {
            return null;
        }
    }
}