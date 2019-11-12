<?php
abstract class DbAbstract {
    protected $prefix;
    global $wpdb;

    protected function __construct() {
        $this->prefix = $wpdb->prefix;
    }

    function getPrefix() {
        return $this->prefix;
    }
}
?>