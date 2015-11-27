<?php
/**
 * @var $site_group Syncee_Site_Group
 * @var $remote_site_collection Syncee_Site_Collection
 * @var $local_site Syncee_Site
 * @var $remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

$local_site                   = $site_group->local_site;
$remote_site_collection       = $site_group->remote_sites;

$total_remote_site_collection = Syncee_Site::getRemoteSiteCollection();

// URLs to synchronization profile views are below:
$synchronize_channels_url = Syncee_Helper::createModuleCpUrl('viewSynchronizeProfileList', array_merge($site_group->getPrimaryKeyNamesValuesMap(), array(
    'comparator_library' => 'Syncee_Entity_Channel_Collection_Library',
    'remote_entity'      => 'Syncee_Request_Remote_Entity_Channel'
)));

$synchronize_channel_fields_url = Syncee_Helper::createModuleCpUrl('viewSynchronizeProfileList', array_merge($site_group->getPrimaryKeyNamesValuesMap(), array(
    'comparator_library' => 'Syncee_Entity_Channel_Field_Collection_Library',
    'remote_entity'      => 'Syncee_Request_Remote_Entity_Channel_Field'
)));

$synchronize_channel_data_url = Syncee_Helper::createModuleCpUrl('viewSynchronizeProfileList', array_merge($site_group->getPrimaryKeyNamesValuesMap(), array(
    'comparator_library' => 'Syncee_Entity_Channel_Data_Collection_Library',
    'remote_entity'      => 'Syncee_Request_Remote_Entity_Channel_Data'
)));

?>
<h3>Local Site: <a href="<?= Syncee_Helper::createModuleCpUrl('editLocalSite', $local_site->getPrimaryKeyNamesValuesMap()) ?>"><?= $local_site->title ?></a></h3>
<br><br>
<h3>Remote Sites</h3>
<?php
    if (!count($total_remote_site_collection)): ?>
        <p>You currently don't have any remote sites set up.  <a href="<?= Syncee_Helper::createModuleCpUrl('newRemoteSite') ?>">Click here to set up one.</a></p>
<?php
    elseif (!count($remote_site_collection)): ?>
        <p>You haven't assigned any remote sites to this local site.  <a href="<?= Syncee_Helper::createModuleCpUrl('editSiteGroup', $site_group->getPrimaryKeyNamesValuesMap()) ?>">Click here to add them to the site group.</a></p>
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

<a href="<?= $synchronize_channels_url ?>">Channels</a>
<a href="<?= $synchronize_channel_fields_url ?>">Channel Fields</a>
<a href="<?= $synchronize_channel_data_url ?>">Channel Data</a>


<?php /*
<form method="post" action="<?= Syncee_Helper::createModuleCpUrl('synchronize', $site_group->getPrimaryKeyNamesValuesMap()) ?>">
    <button type="submit">Synchronize Channels</button>
    <input type="hidden" name="comparator_library" value="Syncee_Entity_Channel_Collection_Library">
    <input type="hidden" name="remote_entity" value="Syncee_Request_Remote_Entity_Channel">
    <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
</form>

<form method="post" action="<?= Syncee_Helper::createModuleCpUrl('synchronize', $site_group->getPrimaryKeyNamesValuesMap()) ?>">
    <button type="submit">Synchronize Channel Fields</button>
    <input type="hidden" name="comparator_library" value="Syncee_Entity_Channel_Field_Collection_Library">
    <input type="hidden" name="remote_entity" value="Syncee_Request_Remote_Entity_Channel_Field">
    <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
</form>

<p>Synchronize Channel Data</p> */ ?>
