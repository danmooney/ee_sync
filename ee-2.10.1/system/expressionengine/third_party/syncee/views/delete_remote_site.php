<?php
/**
 * @var $syncee_remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
Are you sure you'd like to delete <?= $syncee_remote_site->title ?>?

<form method="post">
    <button type="submit">Delete Remote Site</button>
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>