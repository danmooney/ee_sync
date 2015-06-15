<?php

require_once dirname(__FILE__) . '/../_init.php';

class Test_Comparison_Channel_Field extends Syncee_Unit_Test_Case_Abstract
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
        'testTwoSitesWithCompletelySimilarChannelFieldsGivesEmptyComparisonLibrary' => array(
            'animals_channel',
        ),
        'testTwoSitesWithDifferingFieldLabelGivesOneResultInComparisonLibrary' => array(
            'animals_channel',
            'synchronization_tests/animals_channel_field_alter_field_label_in_source'
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

    public function testTwoSitesWithCompletelySimilarChannelFieldsGivesEmptyComparisonLibrary()
    {
        $channel_field_comparison_library = $this->_site_collection->getChannelFieldComparisonCollectionLibrary();
        $this->assertTrue($channel_field_comparison_library->hasNoComparisons(), 'Two sites have no comparisons; they are the same');
    }

    public function testTwoSitesWithDifferingFieldLabelGivesOneResultInComparisonLibrary()
    {
        $channel_field_comparison_library = $this->_site_collection->getChannelFieldComparisonCollectionLibrary();

        $non_empty_channel_field_comparison_library = $channel_field_comparison_library->getNonEmptyComparisonCollectionLibrary();

        $this->assertEqual(count($non_empty_channel_field_comparison_library), 1, 'Number of non-empty channel field comparison collections is 1');

        $channel_field_comparison_collection        = $non_empty_channel_field_comparison_library[0];
        $this->assertEqual(count($channel_field_comparison_collection), 1, 'There is 1 comparison result entity in channel field comparison collection');

        /**
         * @var $channel_field_comparison_entity Syncee_Entity_Comparison
         */
        $channel_field_comparison_entity = $channel_field_comparison_collection[0];
        $this->assertEqual($channel_field_comparison_entity->getComparateColumnName(), 'field_label', 'Lone channel comparison entity\'s comparate column name is "field_label"');
        $this->assertEqual($channel_field_comparison_entity->getSourceValue(), 'Animal Description YO', 'Lone channel comparison entity\'s source value is "Animal Description YO"');
        $this->assertEqual($channel_field_comparison_entity->getTargetValue(), 'Animal Description', 'Lone channel comparison entity\'s target value is "Animal Description"');
        $this->assertEqual($channel_field_comparison_entity->getComparisonResult(), $channel_field_comparison_entity::RESULT_COMPARATE_VALUE_DIFFERS, 'Lone channel field comparison entity is ' . $channel_field_comparison_entity::RESULT_COMPARATE_VALUE_DIFFERS);
    }
}