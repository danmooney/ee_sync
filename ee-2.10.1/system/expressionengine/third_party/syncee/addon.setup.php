<?php

// EE3 setup file

if (!function_exists('lang')) {
    function lang($line, $id = '')
    {
        $CI = get_instance();
        return $CI->lang->line($line, $id);
    }
}

return array(
    'author'         => 'Dan Mooney',
    'author_url'     => 'https://sync-ee.com/',
    'name'           => lang('syncee_module_name'),
    'description'    => lang('syncee_module_description'),
    'version'        => '0.1.0',
    'namespace'      => 'Syncee',
    'settings_exist' => true,
);
