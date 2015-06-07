<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Site_Rsa extends Syncee_Unit_Test_Case_Abstract
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
     * @var Syncee_Site_Rsa
     */
    private $_site_rsa;

    /**
     * @var Syncee_Site
     */
    private $_remote_site;

    protected $_sql_file = '150525_1_ee_sync_FRESH_INSTALL.sql';

    protected $_truncation_setup_type = self::TRUNCATE_SYNCEE_ONLY;

    protected $_seed_data_files = array(
        'animals_channel'
    );

    public function setUp()
    {
        parent::setUp();
        $this->_seedSiteData();
        $this->_mcp      = new Syncee_Mcp();
        $this->_site_rsa = new Syncee_Site_Rsa();

        $this->_site_collection = Syncee_Site_Collection::getAllBySiteId(1);

        $current_local_site   = $this->_site_collection[0];
        $_SERVER['HTTP_HOST'] = parse_url($current_local_site->site_url, PHP_URL_HOST);

        $this->_remote_site   = $this->_site_collection->filterByCondition('isRemote', true);
    }

    public function testPrivateKeyDecryption()
    {
        $site_rsa    = $this->_site_rsa;
        $public_key  = $site_rsa->getPublicKey();
        $private_key = $site_rsa->getPrivateKey();

        $test_str    = 'whatever';

        $site_rsa->getCrypt()->loadKey($public_key);

        $encrypted_test_str = $site_rsa->getCrypt()->encrypt($test_str);

        $this->assertFalse($test_str === $encrypted_test_str, 'Test String is not Identical to Encrypted Test String');
        $this->assertIsA($encrypted_test_str, 'string', 'Encrypted Test String is a String');

        $site_rsa->getCrypt()->loadKey($private_key);
        $this->assertSame($test_str, $site_rsa->getCrypt()->decrypt($encrypted_test_str), 'Decrypted Test String is Equal to original Test String');
    }

    public function testDecryptionOfRemoteApiDataWithCorrectPrivateKeyIsAnArray()
    {
        $mcp         = $this->_mcp;
        $remote_site = $this->_remote_site;

        $mcp->makeRemoteDataApiCallToSite($remote_site, 'channels');

        $data = $mcp->getLastResponseDataDecoded();

        $this->assertTrue(is_array($data), 'Data is properly decrypted and is an array');
    }

    public function testSavingPrivateKey()
    {
        $this->fail('Need to implement ' . __METHOD__);
    }

    public function testSavingPublicKey()
    {
        $this->fail('Need to implement ' . __METHOD__);
    }
}