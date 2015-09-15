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


class Syncee_Site_Synchronization_Profile_Decision extends Syncee_ActiveRecord_Abstract
{
    /**
     * @var Syncee_Site_Synchronization_Profile
     */
    private $_synchronization_profile;

    public function setSynchronizationProfile(Syncee_Site_Synchronization_Profile $synchronization_profile)
    {
        $this->_synchronization_profile = $synchronization_profile;
        return $this;
    }

    public function getSynchronizationProfile()
    {
        if (!isset($this->_synchronization_profile)) {
            $this->_synchronization_profile = Syncee_Site_Synchronization_Profile::findByPk($this->synchronization_profile_id);
        }

        return $this->_synchronization_profile;
    }
}