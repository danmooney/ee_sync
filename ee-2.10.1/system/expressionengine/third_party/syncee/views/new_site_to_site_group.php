<select>
    <option value="" disabled selected>Select a Site</option>
    <?php
        foreach ($ee_sites as $ee_site): ?>
            <option value="<?= $ee_site['site_id'] ?>"><?= $ee_site['site_label'] ?></option>
    <?php
        endforeach ?>
</select>

<p>In order to transmit data between source and target servers, </p>

<?=
    // TODO - maybe store a backup of public key on filesystem?
    base64_encode(serialize(array(
        'ee_site_id'   => 1,
        'site_url'     => $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), // http://ee-10.2.1 or whatever (can't execute actions with admin in URL)
        'use_https'    => '',
        'ip_whitelist' => '',
        'public_key'   => '',
        'action_id'    => '?????'
    )))
?>