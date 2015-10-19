<?php
/**
 * @var $syncee_site_group Syncee_Site_Group
 * @var $remote_site_collection Syncee_Site_Collection
 * @var $local_site Syncee_Site
 * @var $remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

$local_site                   = $syncee_site_group->local_site;
$remote_site_collection       = $syncee_site_group->remote_sites;

$total_remote_site_collection = Syncee_Site::getRemoteSiteCollection();

?>
<h3>Local Site: <a href="<?= Syncee_Helper::createModuleCpUrl('editLocalSite', $local_site->getPrimaryKeyNamesValuesMap()) ?>"><?= $local_site->title ?></a></h3>
<br><br>
<h3>Remote Sites</h3>
<?php
    if (!count($total_remote_site_collection)): ?>
        <p>You currently don't have any remote sites set up.  <a href="<?= Syncee_Helper::createModuleCpUrl('newRemoteSite') ?>">Click here to set up one.</a></p>
<?php
    elseif (!count($remote_site_collection)): ?>
        <p></p>
<?php
    else: ?>
        <ul>
    <?php
        foreach ($remote_site_collection as $remote_site): ?>
            <li>
                <a href="<?= Syncee_Helper::createModuleCpUrl('editRemoteSite', $remote_site->getPrimaryKeyNamesValuesMap()) ?>"><?= $remote_site->title ?></a>
            </li>
    <?php
        endforeach ?>
        </ul>
<?php
    endif ?>
<br><br>
<form method="post" action="<?= Syncee_Helper::createModuleCpUrl('synchronizeSiteGroupChannels', $syncee_site_group->getPrimaryKeyNamesValuesMap()) ?>">
    <button type="submit">Synchronize Channels</button>
    <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
</form>

<form method="post" action="<?= Syncee_Helper::createModuleCpUrl('synchronizeSiteGroupChannelFields', $syncee_site_group->getPrimaryKeyNamesValuesMap()) ?>">
    <button type="submit">Synchronize Channel Fields</button>
    <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
</form>

<p>Synchronize Channel Data</p>
