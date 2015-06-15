<?php

$is_local_db = ($db_number === 1);

if ($is_local_db) {
    require dirname(__FILE__) . '/../plants_channel.php';
}