<?php
/**
 * @var $ee_sites array
 * @var $syncee_local_sites Syncee_Site_Collection
 * @var $syncee_local_site Syncee_Site
 *
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<table>
    <thead>
        <tr>
            <th>Local Site Name</th>
            <th>EE Site ID</th>
            <th>Allows calls from remote sites?</th>
            <?php /* <th>Call over HTTPS?</th> */ ?>
            <th>IP Whitelist</th>
            <th>Syncee Site ID</th>
            <th>Edit</th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach ($syncee_local_sites as $syncee_local_site):
        $primary_key_value_map = array($syncee_local_site->getPrimaryKeyNames(true) => $syncee_local_site->getPrimaryKeyValues(true));
        $ee_site               = $syncee_local_site->getCorrespondingLocalEeSiteRow();
        ?>
        <?php /*<td><a href="<?= Syncee_Helper::createModuleCpUrl('editLocalSite', $primary_key_value_map) ?>"><?= $ee_site->site_label ?></a></td> */ ?>
        <td><?= $ee_site->site_label ?></td>
        <td><?= $syncee_local_site->ee_site_id ?></td>
        <td><?= $syncee_local_site->requests_from_remote_sites_enabled ? 'Yes' : 'No' ?></td>
        <?php /* <td><?= $syncee_local_site->use_https ? 'Yes' : 'No' ?></td> */ ?>
        <td><?= $syncee_local_site->ip_whitelist ?: '<i>(Empty)</i>' ?></td>
        <td><?= $syncee_local_site->getPrimaryKeyValues(true) ?></td>
        <td><a href="<?= Syncee_Helper::createModuleCpUrl('editLocalSite', $primary_key_value_map) ?>">Edit</a></td>
<?php
    endforeach ?>
    </tbody>
</table>
