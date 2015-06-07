<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Remote_Api_Call_Authorization extends Syncee_Unit_Test_Case_Abstract
{
    /**
     * @var Syncee_Mcp
     */
    private $_mcp;

    /**
     * @var Syncee_Site_Collection
     */
    private $_site_collection;

    /**
     * @var Syncee_Site
     */
    private $_remote_site;

    protected $_sql_file = '150525_1_ee_sync_FRESH_INSTALL.sql';

    protected $_truncation_setup_type = self::TRUNCATE_SYNCEE_ONLY;

    public function setUp()
    {
        parent::setUp();

        $this->_seedSiteData();

        $this->_mcp = new Syncee_Mcp();
        $this->_site_collection = Syncee_Site_Collection::getAllBySiteId(1);

        $current_local_site   = $this->_site_collection[0];
        $_SERVER['HTTP_HOST'] = parse_url($current_local_site->site_url, PHP_URL_HOST);

        $this->_remote_site   = $this->_site_collection->filterByCondition('isRemote', true);
    }

    public function testRemoteAndLocalSiteFiltering()
    {
        $remote_site_collection = $this->_site_collection->filterByCondition('isRemote');

        $this->assertNotEqual(count($this->_site_collection), count($remote_site_collection));

        /**
         * @var $site Syncee_Site
         * @var $local_site Syncee_Site
         */
        foreach ($remote_site_collection as $site) {
            $this->assertTrue($site->isRemote());
        }

        $local_site = $this->_site_collection->filterByCondition('isCurrentLocal', true);

        $this->assertFalse($local_site->isEmptyRow());
        $this->assertTrue($local_site->isCurrentLocal());
    }

    public function testRemoteApiCallPassesWithoutAnyWhitelist()
    {
        $mcp         = $this->_mcp;
        $remote_site = $this->_remote_site;

        $response    = $mcp->makeRemoteDataApiCallToSite($remote_site, 'channels');
        $curl_info   = $mcp->getLastCurlInfo();

        $this->assertJson($response);
        $this->assertEqual(200, $curl_info['http_code'], 'HTTP Response status code is 200; %s');

        $decoded_response = json_decode($response, true);

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 200, 'HTTP code is in JSON response and is 200: %s');
    }

    public function testRemoteApiCallFailsWithWhitelistThatHasFailingIp()
    {
        $mcp         = $this->_mcp;
        $remote_site = $this->_remote_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);
        $remote_site->addToIpWhitelist('0.0.0.1')->save();

        $response  = $mcp->makeRemoteDataApiCallToSite($remote_site, 'channels');
        $curl_info = $mcp->getLastCurlInfo();

        $this->assertJson($response);
        $this->assertEqual(403, $curl_info['http_code'], 'HTTP Response status code is 403');

        $decoded_response = json_decode($response, true);

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 403, 'HTTP code is in JSON response and is 403: %s');
    }

    public function testRemoteApiCallPassesWithWhitelistThatPasses()
    {
        $mcp         = $this->_mcp;
        $remote_site = $this->_remote_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);
        $remote_site->addToIpWhitelist('127.0.0.1')->save();

        $response  = $mcp->makeRemoteDataApiCallToSite($remote_site, 'channels');
        $curl_info = $mcp->getLastCurlInfo();

        $this->assertJson($response);
        $this->assertEqual(200, $curl_info['http_code'], 'HTTP Response status code is 200');

        $decoded_response = json_decode($response, true);

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 200, 'HTTP code is in JSON response and is 200: %s');
    }

    public function testRemoteApiCallPassesWithWhitelistThatHasPassingAndFailingIps()
    {
        $mcp         = $this->_mcp;
        $remote_site = $this->_remote_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);
        $remote_site
            ->addToIpWhitelist('127.0.0.1')
            ->addToIpWhitelist('0.0.0.1')
            ->addToIpWhitelist('FE80:0000:0000:0000:0202:B3FF:FE1E:8329')
            ->save()
        ;

        $response    = $mcp->makeRemoteDataApiCallToSite($remote_site, 'channels');
        $curl_info   = $mcp->getLastCurlInfo();

        $this->assertJson($response);
        $this->assertEqual(200, $curl_info['http_code'], 'HTTP Response status code is 200: %s');

        $decoded_response = json_decode($response, true);

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 200, 'HTTP code is in JSON response and is 200: %s');
    }

    public function testRemoteApiCallFailsAfterAddingAndRemovingPassingIp()
    {
        $mcp         = $this->_mcp;
        $remote_site = $this->_remote_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);
        $remote_site->addToIpWhitelist('127.0.0.1')->addToIpWhitelist('0.0.0.1')->save();
        $remote_site->removeFromIpWhitelist('127.0.0.1')->save();

        $response  = $mcp->makeRemoteDataApiCallToSite($remote_site, 'channels');
        $curl_info = $mcp->getLastCurlInfo();

        $this->assertJson($response);
        $this->assertEqual(403, $curl_info['http_code'], 'HTTP Response status code is 403: %s');

        $decoded_response = json_decode($response, true);

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 403, 'HTTP code is in JSON response and is 403: %s');
    }
}