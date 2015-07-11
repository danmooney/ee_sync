<?php
/**
 * @var $syncee_local_site Syncee_Site
 * @var $form Syncee_Form_Abstract
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<div class="site-settings-container">
    <?= $form ?>
</div>
<div class="remote-site-settings-payload-container">
    <label for="remote_site_settings_payload">Want remote sites to get information from this local site?  Copy the encoded settings payload below and paste them into a new remote site configuration on another Syncee installation.</label>
    <textarea id="remote_site_settings_payload" name="remote_site_settings_payload" readonly onclick="this.select()"><?= $syncee_local_site->generateRemoteSiteSettingsPayload() ?></textarea>
</div>