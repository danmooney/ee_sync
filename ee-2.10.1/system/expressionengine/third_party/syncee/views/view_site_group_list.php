<?php
/**
 * @var $site_groups Syncee_Site_Group_Collection
 * @var $site_group Syncee_Site_Group
 * @var $local_syncee_site Syncee_Site
 * @var $paginator Syncee_Paginator
 */
require_once dirname(__FILE__) . '/../_init.php';

?>
<?php
    if (!count($site_groups)): ?>
        <p>You currently don't have any site groups set up.  Click the "New Site Group" button to set up one.</p>
<?php
    endif ?>
    <a class="btn" href="<?= Syncee_Helper::createModuleCpUrl('newSiteGroup') ?>">New Site Group</a><br><br>
<?php
    if (count($site_groups)):
        echo new Syncee_Table(
            new Syncee_Table_Column_Collection(array(
                new Syncee_Table_Column('Site Group Name', 'title', true, 'left', new Syncee_Table_Column_Value_Formatter_Link('editSiteGroup')),
                new Syncee_Table_Column('Local Site', function (Syncee_Site_Group $row) {
                    $local_syncee_site                       = $row->local_site;
                    $local_syncee_site_primary_key_value_map = $local_syncee_site->getPrimaryKeyNamesValuesMap();
                    if ($local_syncee_site->isEmptyRow()) {
                        $html = 'CONFLICT - NONE';
                    } else {
                        $local_ee_site = $local_syncee_site->getCorrespondingLocalEeSiteRow();
                        $html = vsprintf('<a href="%s">%s</a>', array(
                            Syncee_Helper::createModuleCpUrl('editLocalSite', $local_syncee_site_primary_key_value_map),
                            $local_ee_site->site_label
                        ));
                    }

                    return $html;
                }, false, 'center'/*, new Syncee_Table_Column_Value_Formatter_Link('editLocalSite')*/),
                new Syncee_Table_Column('Remote Sites', function (Syncee_Site_Group $row) {
                    /**
                     * @var $remote_site Syncee_Site
                     */
                    $remote_site_collection = $row->getSiteCollection()->filterByCondition(array('is_local' => false));

                    if (!count($remote_site_collection)) {
                        $html = '<i>(No Remote Sites Assigned)</i>';
                    } else {
                        $remote_site_html = array();

                        foreach ($remote_site_collection as $remote_site) {
                            $remote_site_html[] = sprintf(
                                '<li><a href="%s">%s</a></li>',
                                Syncee_Helper::createModuleCpUrl('editRemoteSite', array('site_id' => $remote_site->getPrimaryKeyValues(true))),
                                $remote_site->title
                            );
                        }

                        $html = '<ul>' . implode('', $remote_site_html) . '</ul>';
                    }

                    return $html;
                }),
                new Syncee_Table_Column('Date Created', 'create_datetime', true, 'center', new Syncee_Table_Column_Value_Formatter_Datetime()),
                new Syncee_Table_Column('Date Last Synchronized', 'last_sync_datetime', true, 'center', new Syncee_Table_Column_Value_Formatter_Datetime('<i>Never</i>')),
                new Syncee_Table_Column('Syncee Site Group ID', 'site_group_id', true, 'right'),
                new Syncee_Table_Column('Edit', null, false, 'center', new Syncee_Table_Column_Value_Formatter_Link('editSiteGroup')),
                new Syncee_Table_Column('Delete', null, false, 'center', new Syncee_Table_Column_Value_Formatter_Link('deleteSiteGroup')),
            )),
            $site_groups,
            null,
            $paginator
        );
    endif;