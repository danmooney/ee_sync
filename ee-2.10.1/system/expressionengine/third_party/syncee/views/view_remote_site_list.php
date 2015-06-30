<?php
/**
 * @var $syncee_remote_sites Syncee_Site_Collection
 * @var $syncee_remote_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if (!count($syncee_remote_sites)): ?>
        <p>You currently don't have any remote sites set up.  Click the "New Remote Site" button to set up one.</p>
<?php
    endif ?>
    <a class="btn" href="<?= Syncee_Helper::createModuleCpUrl('newRemoteSite') ?>">New Remote Site</a><br><br>
<?php
    if (count($syncee_remote_sites)): ?>
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>URL</th>
                    <th>EE Site ID</th>
                    <th>EE Action ID</th>
                    <?php /* <th>Call Over HTTPS?</th> */ ?>
                    <?php /* <th>IP Whitelist</th> */ ?>
                    <th>Date Created</th>
                    <th>Syncee Site ID</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
    <?php
        foreach ($syncee_remote_sites as $syncee_remote_site):
            $primary_key_value_map = array($syncee_remote_site->getPrimaryKeyNames(true) => $syncee_remote_site->getPrimaryKeyValues(true));
            ?>
            <tr>
                <?php /* <td><a href="<?= Syncee_Helper::createModuleCpUrl('viewSiteGroup', $primary_key_value_map) ?>"><?= $syncee_remote_site->title ?></a></td> */ ?>
                <td><?= $syncee_remote_site->title ?: '<i>(None)</i>' ?></td>
                <td align="center"><a href="<?= $syncee_remote_site->getSiteUrl() ?>" target="_blank"><?= $syncee_remote_site->getSiteUrl() ?></a></td>
                <td align="right"><?= $syncee_remote_site->ee_site_id ?></td>
                <td align="right"><?= $syncee_remote_site->action_id ?></td>
                <?php /*<td><?= $syncee_remote_site->use_https ? 'Yes' : 'No' ?></td> */ ?>
                <?php /* <td><?= $syncee_remote_site->ip_whitelist ?></td> */ ?>
                <td><?= Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($syncee_remote_site->create_datetime) ?></td>
                <td align="right"><?= $syncee_remote_site->getPrimaryKeyValues(true) ?></td>
                <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('editRemoteSite', $primary_key_value_map) ?>">Edit</a></td>
                <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('deleteRemoteSite', $primary_key_value_map) ?>">Delete</a></td>
            </tr>
    <?php
        endforeach ?>
            </tbody>
        </table>
<?php
    endif;