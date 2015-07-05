<?php
/**
 * @var $syncee_remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<h1><?= "{$syncee_remote_site->getSiteUrl()}" ?></h1>
<h2>(EE Site ID: <?= $syncee_remote_site->ee_site_id ?>)</h2><br>
<p>Are you sure you'd like to delete <strong><?= $syncee_remote_site->title ?></strong>?</p>

<form method="post">
    <button class="btn" type="submit">Delete Remote Site</button>
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>