<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Remote_Api_Call_Response extends Syncee_Unit_Test_Case_Abstract
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
     * @var Syncee_Request
     */
    private $_request;

    protected $_sql_file = '150525_1_ee_sync_FRESH_INSTALL.sql';

    protected $_truncation_setup_type = self::TRUNCATE_ALL;

    protected $_seed_data_files = array(
        'animals_channel'
    );

    public function setUp()
    {
        parent::setUp();

        $this->_seedSiteData();

        $this->_site_collection = Syncee_Site_Collection::getAllBySiteId(1);

        $current_local_site     = $this->_site_collection[0];
        $_SERVER['HTTP_HOST']   = parse_url($current_local_site->site_url, PHP_URL_HOST);
        $this->_remote_site     = $this->_site_collection->filterByCondition('isRemote', true);
        $this->_request         = new Syncee_Request();
    }

    public function testApiCallResponseHasData()
    {
        $remote_site      = $this->_remote_site;
        $request          = $this->_request;
        $response         = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel());
        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['data']) && $decoded_response['data'], 'Data in response exists and is non-empty: %s');
    }

    public function testMissingPublicKeyReturnsBadPublicKeyMessage()
    {
        $remote_site             = $this->_remote_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);
        $remote_site->public_key = 'some bs';
        $remote_site->save();

        $request   = $this->_request;

        $this->expectError('Decryption error');
        $response  = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel());

        $this->assertEqual($response->getStatusCode(), 500, 'Response code is 500');

        $this->fail('Need to assert bad public key message returned in ' . __METHOD__);
    }
}