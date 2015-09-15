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


class Syncee_Site_Synchronization_Profile_Decision_Factory
{
    /**
     *  @var Syncee_Site_Synchronization_Profile
     */
    private $_profile;

    /**
     * @var array
     */
    private $_decision_payload;

    public function __construct(Syncee_Site_Synchronization_Profile $profile, array $decision_payload)
    {
        $this->_profile          = $profile;
        $this->_decision_payload = $decision_payload;
    }

    /**
     * @return Syncee_Site_Synchronization_Profile_Decision
     */
    public function getNewProfileDecision()
    {
        $synchronization_profile_decision = new Syncee_Site_Synchronization_Profile_Decision(array(
            'decision_payload' => $this->_decision_payload
        ));

        $synchronization_profile_decision
            ->setSynchronizationProfile($this->_profile)
        ;

        return $synchronization_profile_decision;
    }
}