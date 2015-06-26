<?php
/**
 * @var $syncee_local_site Syncee_Site
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<div class="site-settings-container">
    <h1>Edit Local Site</h1>
    <h2><?= $syncee_local_site->getCorrespondingLocalEeSiteRow()->site_label ?></h2><br>
    <form method="post">
        <label for="requests_from_remote_sites_enabled">MASTER OVERRIDE: Are remote sites allowed to call this local site?</label><br>
        <select id="requests_from_remote_sites_enabled" name="requests_from_remote_sites_enabled">
            <option value="0">No</option>
            <option value="1" <?= $syncee_local_site->requests_from_remote_sites_enabled ? 'selected' : '' ?>>Yes</option>
        </select>
        <?php /*
        <br><br>
        <label for="use_https">Tell remote sites to use HTTPS when calling this site?<br>(Changing this requires updating remote Syncee installations that have this site's configuration)</label><br>
        <select name="use_https">
            <option value="0">No</option>
            <option value="1" <?= $syncee_local_site->use_https ? 'selected' : '' ?>>Yes</option>
        </select> */ ?>
        <br><br>
        <label for="ip_whitelist">IP Whitelist (If left empty with master override set to 'Yes', any remote site can make requests to this local site and view its encrypted responses.)<br>Enter one IP per line.  CIDR notation will not be converted to IP ranges. <?php // TODO - Add multiple inputs for this field (and lookup how to validate/convert CIDR ranges) ?></label><br>
        <textarea id="ip_whitelist" name="ip_whitelist"><?= $syncee_local_site->ip_whitelist ?></textarea>
        <br><br>
        <button type="submit">Update Local Site Settings</button>
        <input type="hidden" name="XID" value="<?= ee()->csrf->get_user_token() ?>">
        <input type="hidden" name="csrf_token" value="<?= ee()->csrf->get_user_token() ?>">
    </form>
</div>
<div class="remote-site-settings-payload-container">
    <label for="remote_site_settings_payload">Want remote sites to get information from this local site?  Copy the settings below and paste them into a new remote site configuration on another Syncee installation.</label>
    <textarea id="remote_site_settings_payload" name="remote_site_settings_payload" readonly onclick="this.select()"><?= $syncee_local_site->generateRemoteSiteSettingsPayload() ?></textarea>
</div>
<div class="clr"></div>