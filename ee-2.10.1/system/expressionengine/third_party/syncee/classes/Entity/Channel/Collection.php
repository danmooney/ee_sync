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

class Syncee_Entity_Channel_Collection extends Syncee_Collection_Abstract
{
    protected $_row_model = 'Syncee_Entity_Channel';

    /**
     * @var Syncee_Site
     */
    private $_site;

    public function setSite(Syncee_Site $site)
    {
        $this->_site = $site;
    }

    public function getSite()
    {
        return $this->_site;
    }

    public function getChannelByName($channel_name)
    {
        foreach ($this->_rows as $row) {
            if ($row->channel_name === $channel_name) {
                $channel = $row;
                break;
            }
        }

        return isset($channel) ? $channel : false;
    }

    public function getChannelNames()
    {
        $channel_names = array();

        foreach ($this->_rows as $row) {
            $channel_names[] = $row->channel_name;
        }

        return $channel_names;
    }
}