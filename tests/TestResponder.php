<?php
require_once dirname(__FILE__) . "/testBootstrap.php";
require_once dirname(dirname(__FILE__))."/library/Responder.php";
class TestResponder extends Zend_Test_PHPUnit_ControllerTestCase {
  protected $responder;

  public function setUp(){
    $config = new Configuration('tests');
    $this->config = $config->getConfiguration();
    $this->sql = $this->config['sql'];
    $this->db = new PDO('sqlite:test.sqlite');
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->logFile = false;
    if (!empty($this->config['log_file'])) {
      $this->log_file = $this->config['log_file'];
    }
    $this->responder = new Responder(1, $this->config, $this->db, $this->log_file); 
    $this->createTablesAndMockData();
  }

  protected function createTablesAndMockData(){
    $this->createTableQueue();
    $this->createTableMapping();
  }

  protected function createTableQueue(){
    $this->db->exec($this->sqlCreateTableQueue());
    $this->db->exec($this->sqlAddToQueue());
  }

  protected function sqlCreateTableQueue(){
    return "drop table if exists queue;
      CREATE TABLE queue(
        media_id int, 
        source text, 
        destination text, 
        priority int, 
        status text)"; 
  }
  protected function sqlAddToQueue(){
    return " INSERT INTO queue 
    (media_id, source, destination, priority, status)
    VALUES (321, 'src', 'dst', 1, 'queued')";
  }

  protected function createTableMapping(){
    $this->db->exec($this->sqlCreateTableResponseMapper());
    $this->db->exec($this->sqlAddResponseMaps());
  }

  protected function sqlCreateTableResponseMapper(){
    return "drop table if exists response_mapper;
    create table response_mapper(
      provider_id int,
      response text
    )";
  }

  protected function sqlAddResponseMaps(){
    return "delete from response_mapper;
    insert into response_mapper(provider_id, response)
      values (1, 'ok')";
  }

  public function tearDown(){
    unset($this->responder);
    $this->db->exec($this->sql['clean_out_queue']);
  }

  public function testStoresInfoInDatabaseCorrectly(){
    $xml = $this->config['good_xml_response'];
    $reply = $this->responder->handleResponse($xml);
    $stmt = $this->db->prepare('SELECT media_id, status FROM queue');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $actualMediaId = $result['media_id'];
    $actualStatus = $result['status'];
    $expectedMediaId = 321;
    $expectedStatus = 'done_done';
    $this->assertEquals($actualMediaId, $expectedMediaId);
    $this->assertEquals($actualStatus, $expectedStatus);
  }

  public function testResponseWhenThereIsAnError(){
    $xml = $this->config['bad_xml_response'];
    $this->responder->handleResponse($xml);
    $stmt = $this->db->prepare('select status from queue');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $actual = $result['status'];
    $expected = 'Error';
    $this->assertEquals($actual, $expected);
  }

  protected function getLogLine(){
    if ($this->log_file) {
      $line = file_get_contents($this->log_file);
      $parts = explode(']', $line);
      return trim($parts[1]);
    } else {
      return '';
    }
  }
}
