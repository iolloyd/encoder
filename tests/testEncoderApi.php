<?php
require_once dirname(__FILE__) . "/testBootstrap.php";
require_once dirname(dirname(__FILE__))."/library/Encoder.php";
class TestEncoderApi extends Zend_Test_PHPUnit_TestCase {
    protected $encoder;
    protected $source = 'fake_source';
    protected $destination = 'fake_destination';

    public function setUp(){
        $this->db = new PDO('sqlite:test.sqlite');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $config_object = new Configuration('test');
        $this->config = $config_object->getConfiguration();
        $url = $this->config['xml_data']['api_url'];
        $http_client = new Zend_Http_Client($url, $this->config['client_settings']);
        $this->encoder = new Encoder($this->config, $http_client, $this->db);
        $this->sql = $this->config['sql'];
        $this->db->exec($this->sql['create_table_queue']);
    }

    public function tearDown(){
        unset($this->encoder);
    }

    public function testMakeApiRequest(){
        $config = $this->encoder->getConfiguration();
        $source_video = $config['xml_data']['source']; 
        $destination  = $config['xml_data']['format']['destination'];
        $priority = 1;
        $expected = trim(self::CORRECT_XML_RESPONSE);
        $response = $this->encoder->requestEncoding($source_video, $destination, $priority);
        $this->assertGreaterThan(1, $response);
    }

    protected function dummyData(){
        return array(
            'foo' => 'A',
            'bar' => 'B',
            'source' => $this->source,
	    'format' => array(
		    'destination' => $this->destination
	    ),
            'more' => array('fux' => 'C', 'sum' => 'D')
        );
    }

    protected function dummyXml(){
        return "<query><foo>A</foo><bar>B</bar><source>{$this->source}</source><format><destination>{$this->destination}</destination></format><more><fux>C</fux><sum>D</sum></more></query>";
    }

    const CORRECT_XML_RESPONSE =<<<CORRECT
    <response><message>Added</message>
CORRECT;

}
