<?php

$is_local_db = ($db_number === 1);

if ($is_local_db) {
    ee()->db->update('channels', array(
        'channel_lang' => 'spanish',
    ), array(
        'channel_name' => 'animals',
    ));
}
