<?php
abstract class DbAbstract {
    private $prefix;

    public function __construct() {
        global $wpdb;
        $this->prefix = $wpdb->prefix;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    private function getTableName(){
        
        return $name;
    }
}
?>