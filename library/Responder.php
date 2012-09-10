<?php
class Responder {

  public function __construct($provider, $config, $db, $logFile=false){
    $this->provider = $provider;
    $this->config = $config;
    $this->sql = $this->config['sql'];
    $this->db = $db;
    $this->logFile = $logFile;
  }

  public function handleResponse($xml){
    $status = $this->getMapping($xml, 'status');
    $mediaId = $this->getMapping($xml, 'media_id');
    $stmt = $this->db->prepare($this->sql['update_status']);
    $stmt->execute(array(':status' => $status, ':mediaId' => $mediaId));
    $this->handleLogging($xml);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return array(
      'media_id' => $result['media_id'],
      'status'   => $result['status'],
    );
  }

  protected function handleLogging($xml){
    if (true == $this->config['log_raw_xml']) {
      $this->logRawXml($xml);
    }

    if (true == $this->config['log_xml_response']) {
      $this->logResponse($status, $media_id);
    }
  }

  protected function logRawXml($xml){
    file_put_contents($this->config->raw_xml_log, $xml, FILE_APPEND);
  }

  protected function logResponse($status, $media_id){
    if ($this->log_file) {
      $timestamp = date("Y-m-d:h-i-s");
      $message_to_log = "[$timestamp] MediaId: $media_id, Status: $status";
      file_put_contents($this->log_file, $message_to_log, FILE_APPEND);
    }
  }

  protected function getMapping($xml, $tag){
    $mapping = $this->config['get_mapping'];
    $mapping_info = $mapping[$this->provider][$tag];
    $xml =  $this->xmlToArray($xml);
    $provider_tag = $xml->{$mapping_info}; 
    return $provider_tag;
  }

  protected function xmlToArray($xml){
    return json_decode(json_encode( (array) simplexml_load_string($xml) )); 
  }
}

