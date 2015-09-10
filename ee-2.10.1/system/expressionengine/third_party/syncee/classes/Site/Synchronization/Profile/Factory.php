<?php

if (!defined('SYNCEE_PATH')) {
    $current_dir = dirname(__FILE__);
    $i           = 1;

    while (($ancestor_realpath = realpath($current_dir . str_repeat('/..', $i++)) . '/_init.php') && !is_readable($ancestor_realpath)) {
        $is_at_root = substr($ancestor_realpath, -1) === PATH_SEPARATOR;
        if ($is_at_root) {
            break;
        }
    }

    if (!is_readable($ancestor_realpath)) {
        show_error('Could not find _init.php for module');
    }

    require_once $ancestor_realpath;
}

// the requestor
class Syncee_Site_Synchronization_Profile_Factory
{
    /**
     * @var Syncee_Site_Group
     */
    private $_site_group;

    public function __construct(Syncee_Site_Group $site_group)
    {
        $this->_site_group   = $site_group;
    }

    /**
     * TODO - allow for different comparison libraries to be generated
     * @return Syncee_Site_Synchronization_Profile
     */
    public function getNewSynchronizationProfile()
    {
        $site_group                    = $this->_site_group;
        $site_collection               = $site_group->getSiteCollection();

        $channel_comparison_library    = $site_collection->getChannelComparisonCollectionLibrary();

        $synchronization_profile       = new Syncee_Site_Synchronization_Profile();

        $synchronization_profile->local_site_id = $site_group->local_site->getPrimaryKeyValues(true);

        $synchronization_profile
            ->setRequestLogCollection($site_collection->getRequestLogCollection())
            ->setSiteContainer($site_group)
            ->setComparisonCollectionLibrary($channel_comparison_library)
        ;

        return $synchronization_profile;
    }
}