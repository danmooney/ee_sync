<?php

ee()->db->insert(
    'field_groups',
    array(
        'site_id'     => 1,
        'group_name'  => 'Plants'
    )
);

$group_id = ee()->db->insert_id();


ee()->load->library('api');
ee()->api->instantiate('channel_fields');

ee()->api_channel_fields->update_field(array(
    'site_id'            => 1,
    'group_id'           => $group_id,
    'field_name'         => 'plant_description',
    'field_type'         => 'text',
    'field_label'        => 'Plant Description',
    'field_instructions' => '',
    'field_order'        => 1
));

ee()->api->instantiate('channel_structure');
ee()->api_channel_structure->create_channel(array(
    'site_id'            => 1,
    'channel_title'      => 'Plants',
    'channel_name'       => 'plants',
    'url_title_prefix'   => '',
    'comment_expiration' => '',
    'status_group'       => 1,
    'group_order'        => '',
    'channel_lang'       => 'english',
//            'cat_group'          => array(),
    'field_group'        => $group_id,
));