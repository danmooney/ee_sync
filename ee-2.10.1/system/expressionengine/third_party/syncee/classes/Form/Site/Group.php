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

class Syncee_Form_Site_Group extends Syncee_Form_Abstract
{
    protected $_fields = array(
        'title' => array(
            'type' => 'text',
            'label' => 'Enter a Site Group Name',
            'instructions' => '',
            'required' => true,
        ),
        'local_site_id' => array(
            'type' => 'dropdown',
            'label' => 'Choose a Local Site',
            'required' => true,
        ),
        'remote_site_id' => array(
            'type'  => 'dropdown',
            'multi' => true,
            'label' => 'Choose Remote Sites to Synchronize into Local Site',
            'required' => true
        ),
    );

    protected $_button_text_by_method = array(
        'new'  => 'Add New Site Group',
        'edit' => 'Update Site Group',
    );

    public function elementLocalSiteId(Syncee_Field_Dropdown $local_site_id_field)
    {
        // set options
        $options     = array(
            '' => 'Select a Local Site'
        );

        $local_sites = Syncee_Site::getLocalSiteCollection();

        /**
         * @var $local_site Syncee_Site
         */
        foreach ($local_sites as $local_site) {
            $ee_site_row = $local_site->getCorrespondingLocalEeSiteRow();

            $options[$local_site->getPrimaryKeyValues(true)] = $ee_site_row->site_label;
        }


        $local_site_id_field->setOptions($options);

        // set value
        if (is_object($this->_row->local_site)) {
            $local_site_id = $this->_row->local_site->getPrimaryKeyValues(true);
            $local_site_id_field->setValue($local_site_id);
        }
    }

    public function elementRemoteSiteId(Syncee_Field_Dropdown $remote_site_ids_field)
    {
        // set options
        $options     = array(
            '' => 'Select Remote Sites'
        );

        $remote_sites = Syncee_Site::getRemoteSiteCollection();

        /**
         * @var $remote_site Syncee_Site
         */
        foreach ($remote_sites as $remote_site) {
            $options[$remote_site->getPrimaryKeyValues(true)] = $remote_site->title;
        }

        $remote_site_ids_field->setOptions($options);

        // set value(s) (is multi)
        if (is_object($this->_row->remote_sites)) {
            $remote_sites    = $this->_row->remote_sites;
            $remote_site_ids = array();
            foreach ($remote_sites as $remote_site) {
                $remote_site_ids[] = $remote_site->getPrimaryKeyValues(true);
            }

            $remote_site_ids_field->setValue($remote_site_ids);
        }
    }
}