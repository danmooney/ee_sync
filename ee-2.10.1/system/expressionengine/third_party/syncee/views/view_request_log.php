<?php
/**
 * @var $paginator Syncee_Paginator
 * @var $request_log_collection Syncee_Site_Request_Log_Collection
 * @var $request_log Syncee_Site_Request_Log
 */
require_once dirname(__FILE__) . '/../_init.php';

$primary_key_value_map      = $request_log->getPrimaryKeyNamesValuesMap();
$site_primary_key_value_map = $request_log->site->getPrimaryKeyNamesValuesMap();
?>
<div class="site-settings-container">
    <table class="entity-table" data-resizable-table>
        <tbody>
            <tr>
                <td class="label"><span>Site</span></td>
                <td class="value">
                    <a href="<?= Syncee_Helper::createModuleCpUrl($request_log->site->isLocal() ? 'editLocalSite' : 'editRemoteSite', $site_primary_key_value_map) ?>">
                        <?= $request_log->site->title ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="label"><span>Date of Request</span></td>
                <td class="value"><span><?= Syncee_Helper::convertUTCDateToLocalizedHumanDatetime($request_log->create_datetime) ?></span></td>
            </tr>
            <tr>
                <td class="label"><span>Request Entity Type</span></td>
                <td class="value">
                    <span><?= $request_log->request_entity->getName() ?></span>
                </td>
            </tr>
            <tr>
                <td class="label"><span>Response Status Code</span></td>
                <td class="value"><span><?= $request_log->code ?></span></td>
            </tr>
            <tr>
                <td class="label"><span>Response Syncee Version</span></td>
                <td class="value"><span><?= $request_log->version ?: '<i>(N/A)</i>' ?></span></td>
            </tr>
            <tr>
                <td class="label"><span>Response Message</span></td>
                <td class="value"><span><?= $request_log->message ?: '<i>(N/A)</i>' ?></span></td>
            </tr>
            <tr>
                <td class="label"><span>Errors</span></td>
                <td class="value"><span><?= $request_log->errors  ? implode('<br>', $request_log->errors) : '<i>(N/A)</i>' ?></span></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="remote-site-settings-payload-container">
<?php
    if ($request_log->isSuccess()): ?>
        <h1>Request Successful</h1><br>
<?php
    else: ?>
        <h1>Diagnosis</h1>
        <?= implode('<br>', $request_log->diagnosis->getDiagnoses()) ?>
<?php
    endif ?>
    <h2>Raw Decrypted Response from Request</h2><br>
    <div id="remote_site_settings_payload">
        <div id="remote_site_settings_payload_contents"><pre><?= $request_log->raw_response ?  Syncee_Helper::prettyPrintJson($request_log->getRawResponseWithDataDecoded()) : '(Empty Response)' ?></pre></div>
    </div>
</div>
