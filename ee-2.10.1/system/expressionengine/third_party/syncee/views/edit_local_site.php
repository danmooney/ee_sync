<?php
/**
 * @var $syncee_local_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<form method="post">
    <p>Are remote sites allowed to call this local site?</p>
    <select name="requests_from_remote_sites_enabled">
        <option value="0">No</option>
        <option value="1" <?= $syncee_local_site->requests_from_remote_sites_enabled ? 'selected' : '' ?>>Yes</option>
    </select>
    <p>Tell remote sites to use HTTPS when calling this site? (Changing this requires updating remote Syncee installations that have this site's configuration)</p>
    <select name="use_https">
        <option value="0">No</option>
        <option value="1" <?= $syncee_local_site->use_https ? 'selected' : '' ?>>Yes</option>
    </select>
    <p>IP Whitelist <?php // TODO - Add multiple inputs for this field (and lookup how to validate/convert CIDR ranges) ?></p>
    <input type="text" name="ip_whitelist" value="<?= $syncee_local_site->ip_whitelist ?>">
</form>
<div class="remote-site-settings-payload-container">
    <label for="remote_site_settings_payload">Want remote sites to get information from this local site?  Copy the settings below and paste them into a new remote site configuration on another Syncee installation.</label>
    <textarea id="remote_site_settings_payload" name="remote_site_settings_payload"><?= $syncee_local_site->generateRemoteSiteSettingsPayload() ?></textarea>
</div>
<input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
<input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
