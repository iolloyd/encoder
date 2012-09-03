<?php
require_once "spyc.php";

class Configuration {
  private $config;

  public function __construct($file_name){
    $dir = dirname(dirname(__FILE__)) . '/config';
    $file = "$dir/$file_name.yml";
    $this->config = spyc_load_file($file);
  }

  public function getConfiguration(){
    return $this->config;
  }

}

