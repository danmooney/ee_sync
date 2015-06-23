<?php

require_once dirname(__FILE__) . '/../_init.php';

?>
Are you sure you'd like to delete <?= $syncee_site_group->title ?>?

<form method="post">
    <button type="submit">Delete Site Group</button>
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>