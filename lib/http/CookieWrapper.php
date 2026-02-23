<?php
namespace lib\http;

class CookieWrapper {
    public function __construct($name, $value, $time) {
        if($name == null || $value == null  || $time == null ) return null;
        setcookie($name, $value, time() + (86400 * 30), "/");
        var_dump($_COOKIE);
    }

    public function get($name) {
        if($name == null ) return null;
        if(!isset($_COOKIE[$name])) return null;

        return $_COOKIE[$name];
    }
}
?>
