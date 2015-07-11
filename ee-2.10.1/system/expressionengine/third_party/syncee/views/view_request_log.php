<?php
/**
 * @var $paginator Syncee_Paginator
 * @var $request_log_collection Syncee_Site_Request_Log_Collection
 * @var $request_log Syncee_Site_Request_Log
 */
require_once dirname(__FILE__) . '/../_init.php';

$primary_key_value_map      = array($request_log->getPrimaryKeyNames(true) => $request_log->getPrimaryKeyValues(true));
$site_primary_key_value_map = array($request_log->site->getPrimaryKeyNames(true) => $request_log->site->getPrimaryKeyValues(true))
?>
<div class="site-settings-container">
    <table class="entity-table">
        <tbody>
            <tr>
                <td class="label">Site</td>
                <td class="value">
                    <a href="<?= Syncee_Helper::createModuleCpUrl($request_log->site->isLocal() ? 'editLocalSite' : 'editRemoteSite', $site_primary_key_value_map) ?>">
                        <?= $request_log->site->title ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="label">Request Entity Type</td>
                <td class="value">
                    <?= $request_log->request_entity->getName() ?>
                </td>
            </tr>
            <tr>
                <td class="label">Response Status Code</td>
                <td class="value"><?= $request_log->code ?></td>
            </tr>
            <tr>
                <td class="label">Response Syncee Version</td>
                <td class="value"><?= $request_log->version ?: '<i>(N/A)</i>' ?></td>
            </tr>
            <tr>
                <td class="label">Response Message</td>
                <td class="value"><?= $request_log->message ?: '<i>(N/A)</i>' ?></td>
            </tr>
            <tr>
                <td class="label">Errors</td>
                <td class="value"><?= $request_log->errors  ? implode('<br>', $request_log->errors) : '<i>(N/A)</i>' ?></td>
            </tr>
            <tr>
                <td class="label">Date of Request</td>
                <td class="value"><?= Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($request_log->create_datetime) ?></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="remote-site-settings-payload-container">
    <h2>Raw Response from Request</h2><br>
    <div id="remote_site_settings_payload">
        <div id="remote_site_settings_payload_contents"><pre><?= Syncee_Helper::prettyPrintJson($request_log->raw_response) ?></pre></div>
    </div>
</div>
