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
    <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
</form>