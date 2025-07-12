<?php
declare(strict_types=1);
namespace lib\http;

/**
 * SessionWrapper
 *
 * @package
 * @author   Kenzo Coenaerts
 * @link
 */
class SessionWrapper
{
    //private $expiryDate;

    public function __construct() // Default expiry: 1 hour
    {
      if(session_status() == PHP_SESSION_NONE) session_start();    
    }

    /**
    * Attempt to create a session
    *
    * @param string key
    * @param any value
    * @return void
    * @throws SessionException
    */
    //public function create($key, $value) {
    public function set($key, $value) {
        if($key == null) throw new \Exception("Trying to create session with an invalid session key, key: " . $key, 1);

        if(session_status() == PHP_SESSION_ACTIVE)
            $_SESSION[$key] = $value;
    }

    /**
    * Attempt to get session value
    *
    * @param string key
    * @return any value
    * @throws SessionException
    */
    //public function get($key)
    public function get($key) {
      if (!isset($_SESSION[$key])) return null;
      if(!session_status() == PHP_SESSION_ACTIVE) return null;

      return $_SESSION[$key];
    }

    public function destroy() : void
    {
        session_unset();
        session_destroy();
    }
    
    /*
    public function __destruct()
    {
        if(session_status() == PHP_SESSION_ACTIVE) session_destroy();
    }
    */

    //Flash Messages
    //Flash messages allow temporary session-based messages that are automatically removed after being accessed once.
    public function setFlash($key, $message) : void 
    {
        $_SESSION['flash'][$key] = $message;
    }

    public function getFlash($key) : string
    {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]); // Remove after access
            return $message;
        }
        return null;
    }
}
?>
