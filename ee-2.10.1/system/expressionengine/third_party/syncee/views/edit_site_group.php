<?php

require_once dirname(__FILE__) . '/../_init.php';

?>

<form method="post">
    <p>Site Group Name:</p>
    <input type="text" name="title" value="<?= $syncee_site_group->title ?>">
    <button type="submit">Edit Site Group</button>
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>