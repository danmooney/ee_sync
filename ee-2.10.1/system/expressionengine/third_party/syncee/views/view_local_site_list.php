<?php
/**
 * @var $ee_sites array
 * @var $syncee_local_sites Syncee_Site_Collection
 * @var $syncee_local_site Syncee_Site
 *
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<table class="collection-table">
    <thead>
        <tr>
            <th>Label</th>
            <th>EE Site ID</th>
            <th>Allows calls from remote sites?</th>
            <th>IP Whitelist</th>
            <th>Last Call Status Result</th>
            <th>Syncee Site ID</th>
            <th>Edit</th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach ($syncee_local_sites as $syncee_local_site):
        $primary_key_value_map = $syncee_local_site->getPrimaryKeyNamesValuesMap();
        $ee_site               = $syncee_local_site->getCorrespondingLocalEeSiteRow();
        $last_request_log      = $syncee_local_site->last_request_log;

        if ($last_request_log->isEmptyRow()) {
            $last_request_log_status = '(N/A)';
            $last_request_link_html = sprintf('<i>%s</i>', $last_request_log_status);
        } else {
            $last_request_log_status = $last_request_log->isSuccess()
                ? 'SUCCESS'
                : 'ERROR'
            ;

            $last_request_link_html = sprintf(
                '<a href="%s">%s</a>',
                Syncee_Helper::createModuleCpUrl('viewRequestLog', $last_request_log->getPrimaryKeyNamesValuesMap()), // TODO - implement
                $last_request_log_status
            );
        }
        ?>
        <tr>
            <td><a href="<?= Syncee_Helper::createModuleCpUrl('editLocalSite', $primary_key_value_map) ?>"><?= $ee_site->site_label ?></a></td>
            <td align="right"><?= $syncee_local_site->ee_site_id ?></td>
            <td align="center"><?= $syncee_local_site->requests_from_remote_sites_enabled ? 'Yes' : 'No' ?></td>
            <td align="center">
                <?php
                    $ip_whitelist_exploded = $syncee_local_site->getIpWhitelistExploded();
                    $ip_whitelist_count    = count($ip_whitelist_exploded);

                    if ($ip_whitelist_count >= 5) {
                        echo "$ip_whitelist_count IP addresses";
                    } elseif ($ip_whitelist_count) {
                        echo implode('<br>', $ip_whitelist_exploded);
                    } else {
                        echo '<i>(Empty)</i>';
                    }
                ?>
            </td>
            <td align="center"><?= $last_request_link_html ?></td>
            <td align="right"><?= $syncee_local_site->getPrimaryKeyValues(true) ?></td>
            <td align="center"><a href="<?= Syncee_Helper::createModuleCpUrl('editLocalSite', $primary_key_value_map) ?>">Edit</a></td>
        </tr>
<?php
    endforeach ?>
    </tbody>
</table>
