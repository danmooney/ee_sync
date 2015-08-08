<?php
/**
 * @var $syncee_remote_site Syncee_Site
 * @var $form Syncee_Form_Abstract
 */
require_once dirname(__FILE__) . '/../_init.php';
?>
<div class="site-settings-container">
    <h1><?= "{$syncee_remote_site->getSiteUrl()}" ?></h1>
    <h2>(EE Site ID: <?= $syncee_remote_site->ee_site_id ?>)</h2>
    <br><br>
    <?= $form ?>
</div>
<div class="remote-site-settings-payload-container">
    <div style="float: right;">
        <a href="<?= Syncee_Helper::createModuleCpUrl('pingRemoteSite', $syncee_remote_site->getPrimaryKeyNamesValuesMap()) ?>" class="btn btn-secondary btn-ping-site">Ping Site</a>
    </div>
    <div class="clr"></div>
    <br>
    <div id="remote_site_settings_payload">
        <div id="remote_site_settings_payload_contents">
            <p>Click the "Ping Site" button to make a request to the site to check that everything's OK</p>
        </div>
    </div>
    <br>
    <div style="float: right;">
        <a href="<?= Syncee_Helper::createModuleCpUrl('viewRequestLogList', $syncee_remote_site->getPrimaryKeyNamesValuesMap()) ?>">View Ping Logs for this Remote Site</a>
    </div>
</div>
