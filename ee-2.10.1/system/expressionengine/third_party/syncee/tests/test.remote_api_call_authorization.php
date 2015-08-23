<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Remote_Api_Call_Authorization extends Syncee_Unit_Test_Case_Abstract
{
    /**
     * @var Syncee_Site_Collection
     */
    private $_site_collection;

    /**
     * @var Syncee_Site
     */
    private $_remote_site;

    /**
     * @var Syncee_Site
     */
    private $_local_site;

    /**
     * @var Syncee_Request
     */
    private $_request;

    protected $_sql_file = '150525_1_ee_sync_FRESH_INSTALL.sql';

    protected $_truncation_setup_type = self::TRUNCATE_SYNCEE_ONLY;

    public function setUp()
    {
        parent::setUp();

        $this->_seedSiteData();

        $this->_site_collection = Syncee_Site_Group::findByPk(1)->getSiteCollection();

        $this->_remote_site     = $this->_site_collection->filterByCondition('isRemote', true);
        $current_local_site     = $this->_local_site = $this->_site_collection->filterByCondition('isLocal', true);
        $_SERVER['HTTP_HOST']   = $current_local_site->site_host;

        $this->_request       = new Syncee_Request();
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

        $local_site = $this->_site_collection->filterByCondition('isLocal', true);

        $this->assertFalse($local_site->isEmptyRow());
        $this->assertTrue($local_site->isLocal());
    }

    public function testRemoteApiCallPassesWithoutAnyWhitelist()
    {
        $remote_site = $this->_remote_site;
        $request     = $this->_request;

        $response    = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());
        $status_code = $response->getStatusCode();

        $this->assertJson($response->getRawResponse());
        $this->assertEqual(200, $status_code, 'HTTP Response status code is 200; %s');

        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 200, 'HTTP code is in JSON response and is 200: %s');
    }

    public function testRemoteApiCallFailsWithWhitelistThatHasFailingIp()
    {
        /**
         * @var $remote_site Syncee_Site
         */
        $request     = $this->_request;
        $local_site  = $this->_local_site;

        $this->_switchToDatabaseBasedOnSite($this->_remote_site);

        // need to be wary of when changing dbs; primary keys change too.  explicitly fetch the remote (now actually local since db switch) site's row
        $remote_site_thats_actually_local_now_since_db_switch = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => 1), true);

        $remote_site_thats_actually_local_now_since_db_switch->addToIpWhitelist('0.0.0.1')->save();

        $this->_switchToDatabaseBasedOnSite($local_site);

        // get local site's interpretation of remote site again
        $remote_site = $this->_remote_site;

        $response    = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());
        $status_code = $response->getStatusCode();

        $this->assertJson($response->getRawResponse());
        $this->assertEqual(403, $status_code, 'HTTP Response status code is 403: %s');

        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 403, 'HTTP code is in JSON response and is 403: %s');
    }

    public function testRemoteApiCallPassesWithWhitelistThatPasses()
    {
        /**
         * @var $remote_site Syncee_Site
         */
        $request     = $this->_request;
        $remote_site = $this->_remote_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);

        // need to be wary of when changing dbs; primary keys change too.  explicitly fetch the remote (now actually local since db switch) site's row
        $remote_site = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => 1), true);

        $remote_site->addToIpWhitelist('127.0.0.1')->save();

        $response    = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());
        $status_code = $response->getStatusCode();

        $this->assertJson($response->getRawResponse());
        $this->assertEqual(200, $status_code, 'HTTP Response status code is 200: %s');

        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 200, 'HTTP code is in JSON response and is 200: %s');
    }

    public function testRemoteApiCallPassesWithWhitelistThatHasPassingAndFailingIps()
    {
        /**
         * @var $remote_site Syncee_Site
         */
        $request     = $this->_request;
        $remote_site = $this->_remote_site;
        $local_site  = $this->_local_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);

        // need to be wary of when changing dbs; primary keys change too.  explicitly fetch the remote (now actually local since db switch) site's row
        $remote_site_thats_actually_local_now_since_db_switch = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => 1), true);

        $remote_site_thats_actually_local_now_since_db_switch
            ->addToIpWhitelist('127.0.0.1')
            ->addToIpWhitelist('0.0.0.1')
            ->addToIpWhitelist('FE80:0000:0000:0000:0202:B3FF:FE1E:8329')
            ->save()
        ;

        $this->_switchToDatabaseBasedOnSite($local_site);

        // get local site's interpretation of remote site again
        $remote_site = $this->_remote_site;

        $response    = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());
        $status_code = $response->getStatusCode();

        $this->assertJson($response->getRawResponse());
        $this->assertEqual(200, $status_code, 'HTTP Response status code is 200: %s');

        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 200, 'HTTP code is in JSON response and is 200: %s');
    }

    public function testRemoteApiCallFailsAfterAddingAndRemovingPassingIp()
    {
        /**
         * @var $remote_site Syncee_Site
         */
        $request     = $this->_request;
        $remote_site = $this->_remote_site;
        $local_site  = $this->_local_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);

        // need to be wary of when changing dbs; primary keys change too.  explicitly fetch the remote (now actually local since db switch) site's row
        $remote_site = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => 1), true);

        $remote_site->addToIpWhitelist('127.0.0.1')->addToIpWhitelist('0.0.0.1')->save();
        $remote_site->removeFromIpWhitelist('127.0.0.1')->save();

        $this->_switchToDatabaseBasedOnSite($local_site);

        // get local site's interpretation of remote site again
        $remote_site = $this->_remote_site;

        $response    = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());
        $status_code = $response->getStatusCode();

        $this->assertJson($response->getRawResponse());
        $this->assertEqual(403, $status_code, 'HTTP Response status code is 403: %s');

        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 403, 'HTTP code is in JSON response and is 403: %s');
    }

    public function testRemoteApiCallFailsWhenAllRemoteRequestsDisabled()
    {
        /**
         * @var $remote_site Syncee_Site
         */
        $request     = $this->_request;
        $remote_site = $this->_remote_site;
        $local_site  = $this->_local_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);

        // need to be wary of when changing dbs; primary keys change too.  explicitly fetch the remote (now actually local since db switch) site's row
        $remote_site = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => 1), true);

        $remote_site->requests_from_remote_sites_enabled = false;
        $remote_site->addToIpWhitelist('127.0.0.1')->addToIpWhitelist('0.0.0.1')->save();

        $this->_switchToDatabaseBasedOnSite($local_site);

        // get local site's interpretation of remote site again
        $remote_site = $this->_remote_site;

        $response    = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());
        $status_code = $response->getStatusCode();

        $this->assertJson($response->getRawResponse());
        $this->assertEqual(403, $status_code, 'HTTP Response status code is 403: %s');

        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['code']) && $decoded_response['code'] === 403, 'HTTP code is in JSON response and is 403: %s');
    }
}