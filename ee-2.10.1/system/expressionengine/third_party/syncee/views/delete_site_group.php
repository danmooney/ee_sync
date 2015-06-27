<?php
/**
 * @var $syncee_site_group Syncee_Site_Group
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<h1>Delete Site Group</h1><br>
<p>Are you sure you'd like to delete <strong><?= $syncee_site_group->title ?></strong>?</p>
<br>
<form method="post">
    <button class="btn" type="submit">Delete Site Group</button>
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>