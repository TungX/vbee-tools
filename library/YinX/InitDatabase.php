<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Zend/Config/Ini.php';
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/database.ini', 'production');
$config_array = $config->toArray();

//Init database
$database_params = $config_array['database']['params'];
$conn = new mysqli($database_params['host'], $database_params["username"], $database_params["password"]);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$sql = "CREATE DATABASE IF NOT EXISTS " . $database_params['dbname'] ." DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci";
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

mysqli_close($conn);
$conn = new mysqli($database_params['host'], $database_params["username"], $database_params["password"], $database_params["dbname"]);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$database_info = new Zend_Config_Ini(APPLICATION_PATH . '/configs/database_table.ini');
$tables = $database_info->toArray()['database']['table'];

if($conn->query("select 1 from user limit 1") !== FALSE){
    unset($tables['insert_firstUser']);
}

foreach ($tables as $name => $sql) {
    if (!$conn->query($sql) === TRUE) {
        die("Error creating table: " . $conn->error);
    }
}
mysqli_close($conn);