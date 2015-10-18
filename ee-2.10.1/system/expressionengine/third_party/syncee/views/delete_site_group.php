<?php
/**
 * @var $syncee_site_group Syncee_Site_Group
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<p>Are you sure you'd like to delete <strong><?= $syncee_site_group->title ?></strong>?</p>
<br>
<form method="post">
    <button class="btn" type="submit">Delete Site Group</button>
    <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
</form>