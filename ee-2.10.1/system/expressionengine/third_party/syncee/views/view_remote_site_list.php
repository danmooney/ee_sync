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
        <table class="collection-table">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>URL</th>
                    <th>EE Site ID</th>
                    <th>EE Action ID</th>
                    <th>Call Over HTTPS?</th>
                    <th>Last Call Status Result</th>
                    <th>Date Created</th>
                    <th>Syncee Site ID</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
    <?php
        foreach ($syncee_remote_sites as $syncee_remote_site):
            $primary_key_value_map = $syncee_remote_site->getPrimaryKeyNamesValuesMap();
            $last_request_log      = $syncee_remote_site->last_request_log;

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
                <td><a href="<?= Syncee_Helper::createModuleCpUrl('editRemoteSite', $primary_key_value_map) ?>"><?= $syncee_remote_site->title ?: '<i>(None)</i>' ?></a></td>
                <td align="center"><a href="<?= $syncee_remote_site->getSiteUrl() ?>" target="_blank"><?= $syncee_remote_site->getSiteUrl() ?></a></td>
                <td align="right"><?= $syncee_remote_site->ee_site_id ?></td>
                <td align="right"><?= $syncee_remote_site->action_id ?></td>
                <td align="center"><?= $syncee_remote_site->use_https ? 'Yes' : 'No' ?></td>
                <td align="center"><?= $last_request_link_html ?></td>
                <td align="center"><?= Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($syncee_remote_site->create_datetime) ?></td>
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