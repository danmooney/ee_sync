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
        'testTwoSitesWithDifferingChannelLangGivesOneResultInComparisonLibrary' => array(
            'animals_channel',
            'synchronization_tests/animals_channel_alter_channel_lang_in_target'
        ),
        'testTargetSiteWithMoreChannelsThanSourceSiteGivesComparisons' => array(
            'animals_channel',
            'synchronization_tests/plants_channel_init_target'
        ),
    );

    public function setUp()
    {
        parent::setUp();

        $this->_seedSiteData();

        $this->_mcp             = new Syncee_Mcp();
        $this->_site_collection = Syncee_Site_Group::findByPk(1)->getSiteCollection();

        $this->_remote_site     = $this->_site_collection->filterByCondition('isRemote');
        $current_local_site     = $this->_site_collection->filterByCondition('isLocal', true);
        $_SERVER['HTTP_HOST']   = parse_url($current_local_site->site_url, PHP_URL_HOST);
    }

    public function testTwoSitesWithCompletelySimilarChannelGivesEmptyComparisonLibrary()
    {
        $channel_comparison_library = $this->_site_collection->getChannelComparisonCollectionLibrary();

        $this->assertTrue($channel_comparison_library->hasNoComparisons(), 'Two sites have no comparisons; they are the same');
    }

    public function testTwoSitesWithDifferingChannelLangGivesOneResultInComparisonLibrary()
    {
        $channel_comparison_library           = $this->_site_collection->getChannelComparisonCollectionLibrary();
        $non_empty_channel_comparison_library = $channel_comparison_library->getNonEmptyComparisonCollectionLibrary();

        $this->assertEqual(count($non_empty_channel_comparison_library), 1, 'Number of non-empty channel comparison collections is 1: %s');

        $this->assertEqual($channel_comparison_library->getTotalComparisonEntityCountAcrossAllCollections(), 1, 'Total Comparison Entity count across all collections is 1: %s');

        $channel_comparison_collection        = $non_empty_channel_comparison_library[0];
        $this->assertEqual(count($channel_comparison_collection), 1, 'There is 1 comparison result entity in channel comparison collection: %s');

        /**
         * @var $channel_comparison_entity Syncee_Entity_Comparison
         * @var $channel_comparison_collection Syncee_Entity_Comparison_Collection
         */
        $channel_comparison_entity            = $channel_comparison_collection->getComparisonEntityByComparateColumnName('channel_lang');
        $this->assertEqual($channel_comparison_entity->getComparateColumnName(), 'channel_lang', 'Lone channel comparison entity\'s comparate column name is "channel_lang": %s');
        $this->assertEqual($channel_comparison_entity->getSourceValue(), 'english', 'Lone channel comparison entity\'s source value is "english": %s');
        $this->assertEqual($channel_comparison_entity->getTargetValue(), 'spanish', 'Lone channel comparison entity\'s target value is "spanish": %s');
        $this->assertEqual($channel_comparison_entity->getComparisonResult(), $channel_comparison_entity::RESULT_COMPARATE_VALUE_DIFFERS, 'Lone channel comparison entity is ' . $channel_comparison_entity::RESULT_COMPARATE_VALUE_DIFFERS . ': %s');
    }

    public function testTargetSiteWithMoreChannelsThanSourceSiteGivesComparisons()
    {
        $channel_comparison_library = $this->_site_collection->getChannelComparisonCollectionLibrary();

        $this->assertFalse($channel_comparison_library->hasNoComparisons(), 'Two sites are not exactly the same');

        $channel_comparison_collection = $channel_comparison_library->getComparisonCollectionByUniqueIdentifierValue('plants');

        $this->assertIsA($channel_comparison_collection, 'Syncee_Entity_Comparison_Collection', 'Channel comparison collection fetched by unique identifier value of "plants" exists and is an object: %s');

        $this->assertEqual($channel_comparison_collection->getComparisonResult(), $channel_comparison_collection::RESULT_ENTITY_MISSING_IN_SOURCE, 'Channel "plants" exists in target but not in source: %s');

        $channel_comparison_entity = $channel_comparison_collection->getComparisonEntityByComparateColumnName('channel_id');
        $this->assertIsA($channel_comparison_entity, 'Syncee_Entity_Comparison', 'Channel comparison entity fetched by comparate column name of "channel_id" exists and is an object: %s');

        $this->assertEqual($channel_comparison_entity->getComparateColumnName(), 'channel_id', 'Comparison entity fetched has a comparate column name of "channel_id": %s');
        $this->assertTrue(is_numeric($channel_comparison_entity->getTargetValue()), 'Channel id of comparison entity is numeric in target');
        $this->assertEqual($channel_comparison_entity->getSourceValue(), null, 'Channel id of comparison entity is null in source');
        $this->assertEqual($channel_comparison_entity->getComparisonResult(), $channel_comparison_entity::RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE, 'Comparison result of entity is RESULT_COMPARATE_COLUMN_MISSING_IN_SOURCE: %s');

    }
}