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

class Syncee_Mcp_Site_Group_Synchronize extends Syncee_Mcp_Site_Group
{
    /**
     * The GET method for viewing a synchronization_profile_id
     */
    public function synchronize()
    {
        $synchronization_profile_id = ee()->input->get('synchronization_profile_id');

        /**
         * @var $synchronization_profile Syncee_Site_Synchronization_Profile
         */
        $synchronization_profile    = Syncee_Site_Synchronization_Profile::findByPk($synchronization_profile_id);

        if ($synchronization_profile->isEmptyRow()) {
            die('Could not find synchronization profile'); // TODO
        }

        $site_collection    = $synchronization_profile->getSiteContainer();

        $comparison_library = $synchronization_profile->getComparisonCollectionLibrary();

        // sort collections by source site primary key.
        // this is to have a known and predictable way to iterate over collections and get everything in the right order.
        $comparison_library->sortByCallback(function (Syncee_Entity_Comparison_Collection $a, Syncee_Entity_Comparison_Collection $b) {
            return $a->getSource()->getSite()->getPrimaryKeyValues(true) - $b->getSource()->getSite()->getPrimaryKeyValues(true);
        });

        $page_title = sprintf('Synchronize %s', ucwords($synchronization_profile->getEntity()->getName()) . 's');
        Syncee_View::setPageTitle($page_title, true);

        return Syncee_View::render(__FUNCTION__, array(
            'synchronization_profile'    => $synchronization_profile,
            'site_collection'            => $site_collection,
            'entity_comparison_library'  => $comparison_library,
        ), $this);
    }

    public function synchronizePOST()
    {
        $site_group_id                   = ee()->input->get_post('site_group_id');
        $comparator_library              = ee()->input->get_post('comparator_library');
        $remote_entity                   = ee()->input->get_post('remote_entity');

        /**
         * @var $site_group Syncee_Site_Group
         */
        $site_group                      = Syncee_Site_Group::findByPk($site_group_id);

        if ($site_group->isEmptyRow()) {
            // TODO
        }

        $synchronization_profile_factory = new Syncee_Site_Synchronization_Profile_Factory(
            $site_group,
            new $comparator_library(),
            new $remote_entity()
        );

        $synchronization_profile         = $synchronization_profile_factory->getNewSynchronizationProfile();

        $synchronization_profile->save();

        Syncee_Helper::redirect('synchronize', array(
            'synchronization_profile_id' => $synchronization_profile->getPrimaryKeyValues(true)
        ), $this, false);
    }

    public function synchronizeFixPOST()
    {
        $synchronization_profile_id = ee()->input->get_post('synchronization_profile_id');
        $synchronization_profile    = Syncee_Site_Synchronization_Profile::findByPk($synchronization_profile_id);

        if ($synchronization_profile->isEmptyRow()) {
            die('Could not find synchronization profile'); // TODO
        }

        $payload                    = json_decode(ee()->input->post('payload'), true);

        if (!is_array($payload)) {
            die('no payload'); // TODO
        }

        $synchronization_profile_decision_factory = new Syncee_Site_Synchronization_Profile_Decision_Factory($synchronization_profile, $payload);
        $synchronization_profile_decision         = $synchronization_profile_decision_factory->getNewProfileDecision();

        $synchronization_profile_decision->save();

        // Execute the merge!
        try {
            $synchronization_profile_decision->execute();
        } catch (Exception $e) {
            // TODO
            throw $e;
        }

        Syncee_Helper::redirect('synchronize', array(
            'synchronization_profile_id' => $synchronization_profile->getPrimaryKeyValues(true)
        ), $this, 'Data has been merged into local site');
    }
}