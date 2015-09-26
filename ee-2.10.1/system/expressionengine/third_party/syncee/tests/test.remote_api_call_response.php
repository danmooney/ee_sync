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
     * @var Syncee_Site
     */
    private $_local_site;

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

        $this->_site_collection = Syncee_Site_Group::findByPk(1)->getSiteCollection();

        $this->_remote_site     = $this->_site_collection->filterByCondition('isRemote', true);
        $current_local_site     = $this->_local_site = $this->_site_collection->filterByCondition('isLocal', true);
        $_SERVER['HTTP_HOST']   = $current_local_site->site_host;
        $this->_request         = new Syncee_Request();
    }

    public function testApiCallResponseHasData()
    {
        $remote_site      = $this->_remote_site;
        $request          = $this->_request;
        $response         = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());
        $decoded_response = $response->getResponseDecoded();

        $this->assertTrue(isset($decoded_response['data']) && $decoded_response['data'], 'Data in response exists and is non-empty: %s');
    }

    public function testMissingPublicKeyReturnsBadPublicKeyMessage()
    {
        $remote_site             = $this->_remote_site;

        $this->_switchToDatabaseBasedOnSite($remote_site);

        // need to be wary of when changing dbs; primary keys change too.  explicitly fetch the remote (now actually local since db switch) site's row
        $remote_site = Syncee_Site::getLocalSiteCollection()->filterByCondition(array('ee_site_id' => 1), true);

        $remote_site->public_key = 'some bs';
        $remote_site->save();

        $this->_switchToDatabaseBasedOnSite($this->_local_site);

        // get local site's interpretation of remote site again
        $remote_site = $this->_remote_site;

        $request   = $this->_request;

        $this->expectError('Decryption error');
        $response = $request->makeEntityCallToSite($remote_site, new Syncee_Request_Remote_Entity_Channel(), new Syncee_Site_Request_Log());

        $this->assertEqual($response->getStatusCode(), 500, 'Response code is 500: %s');

        $this->assertEqual(
            Syncee_Lang::resolveTranslationToConstant($response->getMessage()),
            'BAD_PUBLIC_KEY',
            'Bad public key message returned in remote response and resolved to constant by language parser.'
        );
    }

    public function testWrongActionIdOnRemoteSiteReturnsSomethingOrOther()
    {
        // TODO
    }

}