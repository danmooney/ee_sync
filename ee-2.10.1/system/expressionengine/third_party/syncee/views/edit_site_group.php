<?php
/**
 * @var $ee_sites array
 * @var $syncee_site_group Syncee_Site_Group
 */
require_once dirname(__FILE__) . '/../_init.php';

?>

<form method="post">
    <label for="title">Enter your Site Group Name</label><br>
    <input id="title" type="text" name="title" value="<?= $syncee_site_group->title ?>"><br><br>
    <label for="ee_site_id">Choose a Local Site</label><br>
    <select id="ee_site_id" name="ee_site_id">
        <option value="" disabled selected>Select a Local Site</option>
    <?php
        foreach ($ee_sites as $ee_site):
            $local_syncee_site = $syncee_site_group->getSiteCollection()->filterByCondition(array('is_local' => true), true); ?>
            <option <?= $local_syncee_site->getPrimaryKeyValues(true) === $ee_site->site_id ? 'selected="selected"' : '' ?> value="<?= $ee_site->site_id ?>"><?= $ee_site->site_label ?></option>
    <?php
        endforeach ?>
    </select>
    <br><br><br>
    <button class="btn" type="submit"><?= $syncee_site_group->isEmptyRow() ? 'Add New' : 'Update' ?> Site Group</button>
    <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
    <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
</form>