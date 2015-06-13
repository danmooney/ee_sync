<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Comparison_Channel extends Syncee_Unit_Test_Case_Abstract
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

    protected $_truncation_setup_type = self::TRUNCATE_ALL;

    protected $_seed_data_files = array(
        'testTwoSitesWithCompletelySimilarChannelGivesEmptyComparisonLibrary' => array(
            'animals_channel',
        ),
        'testTwoSitesWithDifferingChannelDataGivesOneResultInComparisonLibrary' => array(
            'animals_channel',
            'synchronization_tests/animals_channel_alter_source_db'
        ),
    );

    public function setUp()
    {
        parent::setUp();

        $this->_seedSiteData();

        $this->_mcp             = new Syncee_Mcp();
        $this->_site_collection = Syncee_Site_Collection::getAllBySiteId(1);

        $current_local_site     = $this->_site_collection[0];
        $_SERVER['HTTP_HOST']   = parse_url($current_local_site->site_url, PHP_URL_HOST);
        $this->_remote_site     = $this->_site_collection->filterByCondition('isRemote', true);
    }

    public function testTwoSitesWithCompletelySimilarChannelGivesEmptyComparisonLibrary()
    {
        $channel_comparison_library = $this->_site_collection->getChannelComparisonCollectionLibrary();

        $this->assertTrue($channel_comparison_library->hasNoComparisons(), 'Two sites have no comparisons; they are the same');
    }

    public function testTwoSitesWithDifferingChannelDataGivesOneResultInComparisonLibrary()
    {
        $this->fail('Need to implement ' . __METHOD__);

        $channel_comparison_library = $this->_site_collection->getChannelComparisonCollectionLibrary();

        $this->assertTrue($channel_comparison_library->hasNoComparisons(), 'Two sites have no comparisons; they are the same');
    }
}