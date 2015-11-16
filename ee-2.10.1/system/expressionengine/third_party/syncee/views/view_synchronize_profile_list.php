<?php
/**
 * @var $site_group Syncee_Site_Group
 * @var $synchronization_profile_collection Syncee_Collection_Generic
 * @var $remote_entity Syncee_Request_Remote_Entity_Abstract
 * @var $comparator_library Syncee_Collection_Library_Comparator_Abstract
 * @var $remote_entity_name string
 * @var $paginator Syncee_Paginator
 */
require_once dirname(__FILE__) . '/../_init.php';

?>

<?php
    if (!count($synchronization_profile_collection)): ?>
        <p>You haven't generated any <?= strtolower($remote_entity->getName()) ?> synchronization profiles yet.  Click the "Generate New <?= ucwords($remote_entity->getName()) ?> Synchronization Profile" to generate one.</p>
<?php
    endif ?>
    <a class="btn btn-secondary" href="<?= Syncee_Helper::createModuleCpUrl('synchronizeSiteGroup', $site_group->getPrimaryKeyNamesValuesMap()) ?>">Back to main Site Group Synchronization</a>
    <br><br>

    <form method="post" action="<?= Syncee_Helper::createModuleCpUrl('synchronize', $site_group->getPrimaryKeyNamesValuesMap()) ?>">
        <button class="btn" type="submit">Generate New <?= ucwords($remote_entity->getName()) ?> Synchronization Profile</button>
        <input type="hidden" name="comparator_library" value="<?= get_class($comparator_library) ?>">
        <input type="hidden" name="remote_entity" value="<?= get_class($remote_entity) ?>">
        <?= Syncee_View::outputCsrfHiddenFormInputs() ?>
    </form>
    <br><br>
<?php
    if (count($synchronization_profile_collection)):
        echo new Syncee_Table(
            new Syncee_Table_Column_Collection(array(
                new Syncee_Table_Column('Synchronization Profile ID', 'synchronization_profile_id', true, 'right', new Syncee_Table_Column_Value_Formatter_Link('synchronize')),
                new Syncee_Table_Column('Date Created', 'create_datetime', true, 'center', new Syncee_Table_Column_Value_Formatter_Datetime())
            )),
            $synchronization_profile_collection,
            null,
            $paginator
        );
    endif;