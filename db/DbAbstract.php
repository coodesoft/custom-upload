<?php
abstract class DbAbstract {

    public static function getPrefix() {
        global $wpdb;
        return $wpdb->prefix;
    }

    public static function getTableName(){
        return [
            "access" => "cu_access",
            "clientes" => "cu_clientes",
            "default" => "cu_default_files",
            "files" => "cu_files",
            "history" => "cu_history",
            "sucursales" => "cu_sucursales"
        ];
    }
    
    public static function getTable($name){
        $tables = self::getTableName();
        $result = self::getPrefix();
        foreach ($tables as $key => $value) {
            if ($key == $name){
                $result.= $tables[$name];
            }
        }
        return $result;
    }
}
?>