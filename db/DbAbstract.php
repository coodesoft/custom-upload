<?php
abstract class DbAbstract {
    protected $prefix;

    protected function __construct() {
        global $wpdb;
        $this->prefix = $wpdb->prefix;
    }

    /*
    function getPrefix() {
        return $this->prefix;
    } */
}
?>