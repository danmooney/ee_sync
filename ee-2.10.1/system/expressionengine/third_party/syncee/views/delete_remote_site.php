<?php
/**
 * @var $syncee_remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<h1>Delete Remote Site</h1><br>
<p>Are you sure you'd like to delete <?= $syncee_remote_site->title ?>?</p>

<form method="post">
    <button class="btn" type="submit">Delete Remote Site</button>
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>