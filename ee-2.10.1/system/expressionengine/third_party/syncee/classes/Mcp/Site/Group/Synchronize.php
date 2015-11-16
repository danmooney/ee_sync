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
    public function viewSynchronizeProfileList()
    {
        $site_group_id                   = ee()->input->get_post('site_group_id');
        $comparator_library              = ee()->input->get_post('comparator_library');
        $remote_entity                   = ee()->input->get_post('remote_entity');

        $paginator                       = new Syncee_Paginator_Synchronization_Profile($_GET, $this);

        $synchronize_profile_collection  = Syncee_Site_Synchronization_Profile::findAllByCondition(array(
            'site_group_id'                 => $site_group_id,
            'entity_class_name'             => $remote_entity,
            'comparator_library_class_name' => $comparator_library,
        ), $paginator);

        $site_group = Syncee_Site_Group::findByPk($site_group_id);

        if ($site_group->isEmptyRow()) {
            // TODO - throw
        }

        /**
         * @var $remote_entity_obj Syncee_Request_Remote_Entity_Abstract
         */
        $remote_entity_obj  = new $remote_entity();
        $remote_entity_name = ucwords($remote_entity_obj->getName()) . 's';

        $page_title         = "Synchronize $remote_entity_name: <strong>{$site_group->title}</strong>";

        Syncee_View::setPageTitle($page_title, true);

        return Syncee_View::render(__FUNCTION__, array(
            'site_group'                         => $site_group,
            'synchronization_profile_collection' => $synchronize_profile_collection,
            'comparator_library'                 => new $comparator_library(),
            'remote_entity'                      => new $remote_entity(),
            'remote_entity_name'                 => $remote_entity_name,
            'paginator'                          => $paginator
        ), $this);
    }

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

        $remote_entity_name = ucwords($synchronization_profile->getEntity()->getName());
        $site_group         = Syncee_Site_Group::findByPk($synchronization_profile->site_group_id);

        $page_title         = sprintf('Synchronize %s', $remote_entity_name . 's');

        if (!$site_group->isEmptyRow()) {
            $page_title .= ": <strong>{$site_group->title}</strong>";
        }

        Syncee_View::setPageTitle($page_title, true);

        // set $_GET params so menu active state can be resolved
        $_GET['site_group_id']      = $site_group->getPrimaryKeyValues(true) ?: 'PLACEHOLDER_JUST_TO_TRIGGER_MENU';
        $_GET['comparator_library'] = $synchronization_profile->getComparatorLibrary();
        $_GET['remote_entity']      = $synchronization_profile->getEntityName();

        return Syncee_View::render(__FUNCTION__, array(
            'site_group'                 => $site_group,
            'synchronization_profile'    => $synchronization_profile,
            'site_collection'            => $site_collection,
            'entity_comparison_library'  => $comparison_library,
            'remote_entity_name'         => $remote_entity_name
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

        /**
         * @var $synchronization_profile Syncee_Site_Synchronization_Profile
         */
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

        // Execute the merge and update site group last_sync_datetime
        try {
            ee()->db->trans_begin();

            $synchronization_profile_decision->execute();
            if (($site_group = Syncee_Site_Group::findByPk($synchronization_profile_decision->getSynchronizationProfile()->site_group_id)) &&
                !$site_group->isEmptyRow()
            ) {
                $site_group->last_sync_datetime = gmdate('Y-m-d H:i:s');
                $site_group->save();
            }

            ee()->db->trans_commit();
        } catch (Exception $e) {
            ee()->db->trans_rollback();

            // TODO
            throw $e;
        }

        Syncee_Helper::redirect(
            'synchronize',
            array(
                'synchronization_profile_id' => $synchronization_profile->getPrimaryKeyValues(true)
            ),
            $this,
            sprintf('%s have been merged into local site', ucwords($synchronization_profile->getEntity()->getName()) . 's')
        );
    }

    private function _getHumanReadableEntityName()
    {
        // TODO?
    }
}