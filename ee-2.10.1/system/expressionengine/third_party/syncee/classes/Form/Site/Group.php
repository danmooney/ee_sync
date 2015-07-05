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
        'ee_site_id' => array(
            'type' => 'dropdown',
            'label' => 'Choose a Local Site',
            'required' => true,
        ),
    );

    protected $_button_text_by_method = array(
        'new'  => 'Add New Site Group',
        'edit' => 'Update Site Group',
    );

    public function elementEeSiteId(Syncee_Field_Dropdown $ee_site_id)
    {
        $options     = array(
            '' => 'Select a Local Site'
        );
        $local_sites = Syncee_Site::getLocalSiteCollection();

        /**
         * @var $local_site Syncee_Site
         */
        foreach ($local_sites as $local_site) {
            $ee_site_row = $local_site->getCorrespondingLocalEeSiteRow();

            $options[$ee_site_row->site_id] = $ee_site_row->site_label;
        }

        $ee_site_id->setOptions($options);
    }
}