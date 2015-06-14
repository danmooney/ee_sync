<?php

$is_local_db = ($db_number === 1);

if (!$is_local_db) {
    ee()->db->update('channel_fields', array(
        'field_label' => 'Animal Description YO',
    ), array(
        'field_name' => 'animal_description',
    ));
}
