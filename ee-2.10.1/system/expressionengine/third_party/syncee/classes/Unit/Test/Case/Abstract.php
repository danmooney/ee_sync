<?php

if (!defined('SYNCEE_PATH')) {
    $current_dir = dirname(__FILE__);
    $i           = 1;

    while (($ancestor_realpath = realpath($current_dir . str_repeat('/..', $i++)) . '/_init.php') && !is_readable($ancestor_realpath)) {
        $is_at_root = substr($ancestor_realpath, -1) === PATH_SEPARATOR;
        if ($is_at_root) {
            break;
        }
    }

    if (!is_readable($ancestor_realpath)) {
        show_error('Could not find _init.php for module');
    }

    require_once $ancestor_realpath;
}


abstract class Syncee_Unit_Test_Case_Abstract extends Testee_Unit_Test_Case
{
    const TRUNCATE_ALL         = 'TRUNCATE_ALL';
    const TRUNCATE_SYNCEE_ONLY = 'TRUNCATE_SYNCEE_ONLY';

    private $_original_db_name;

    protected $_sql_file;

    protected $_databases;

    protected $_http_host_running_tests;

    protected $_truncation_setup_type = self::TRUNCATE_ALL;

    protected $_seed_data_files = array();

    public function setUp()
    {
        $this->_http_host_running_tests = $_SERVER['HTTP_HOST'];
        $this->_installFreshDatabases();
        $this->_switchToDatabaseBasedOnNumber();
    }

    public function tearDown()
    {
        $_SERVER['HTTP_HOST'] = $this->_http_host_running_tests;
        $this->_truncateTables();
        $this->_switchToDatabaseBasedOnNumber(1);
    }

    public function assertJson($compare, $message = '%s')
    {
        return $this->assert(new Syncee_Json_Expectation(), $compare, $message);
    }

    protected function _getSqlPathname()
    {
        if (!$this->_sql_file) {
            return $this->fail('No sql_file property defined in class ' . __CLASS__);
        }

        $sql_file_pathname = SYNCEE_PATH_TESTS . '/schema/sql/' . $this->_sql_file;

        if (!is_file($sql_file_pathname)) {
            return $this->fail('Unable to find sql file ' . $this->_sql_file);
        } elseif (!is_readable($sql_file_pathname)) {
            return $this->fail('Unable to read sql file ' . $this->_sql_file);
        }

        return $sql_file_pathname;
    }

    protected function _fetchFromConfig($config_item, $fail_on_not_found = true)
    {
        $config_item          = str_replace('.', '/', strtolower($config_item));
        $config_item_pathname = SYNCEE_PATH_TESTS . '/configs/' . $config_item . '.php';

        if (!is_file($config_item_pathname)) {
            if (!$fail_on_not_found) {
                return false;
            }

            return $this->fail('Unable to find config item: ' . $config_item);
        }

        $return_value = include $config_item_pathname;

        return $return_value;
    }

    protected function _switchToDatabaseBasedOnNumber($num = null)
    {
        if ($num === null) {
            $db_name = $this->_original_db_name;
        } else {
            $db_name = $this->_fetchFromConfig("database.connection.db$num");
        }

        ee()->db->database = $db_name;
        ee()->db->db_select($db_name);
    }

    protected function _switchToDatabaseBasedOnSite(Syncee_Site $site)
    {
        preg_match('#(\d+)#', parse_url($site->getSiteUrl(), PHP_URL_HOST), $matches);
        $this->_switchToDatabaseBasedOnNumber($matches[0]);
    }

    protected function _seedSiteData()
    {
        $i         =  1;
        $db_number =& $i;

        $test_stack                    = $this->reporter->getTestList();
        $current_test_method_to_be_run = end($test_stack);

        $sites_by_site_url             = array();
        $local_sites_by_db_connection  = array();
        $site_groups_by_db_connection  = array();

        while ($db_name = $this->_fetchFromConfig("database.connection.db$i", false)) {
            $this->_switchToDatabaseBasedOnNumber($i);

            $j = 1;

            /**
             * @var $site_group Syncee_Site_Group
             * @var $site Syncee_Site
             */
            while ($site_url = $this->_fetchFromConfig("site.url$j", false)) {
                if (!isset($sites_by_site_url[$site_url])) {
                    $sites_by_site_url[$site_url] = $site = new Syncee_Site();

                    $site->site_url               = $site_url;
                    $site->is_local               = $j === $i;

                    if ($site->isLocal()) {
                        $local_sites_by_db_connection[$i] = $site;
                    } else {
                        // if site is remote, switch to database based on $j (remember, if local, $j === $i) and get the action id and all that jazz by saving the now local site on the local db
                        $this->_switchToDatabaseBasedOnNumber($j);

                        $site->is_local = true;
                        $site->save();

                        // revert back to previous database
                        $this->_switchToDatabaseBasedOnNumber($i);
                        $site->is_local = false;
                    }
                }

                $site           = $sites_by_site_url[$site_url];
                $site->is_local = $j === $i;

                // If site is considered remote, simulate transfer of the remote site's encoded settings payload
                if ($site->isRemote()) {
                    $site = Syncee_Site::getByDecodingRemoteSiteSettingsPayload($site->generateRemoteSiteSettingsPayload());
                }

                if (!isset($site_groups_by_db_connection[$i])) {
                    $site_group          = $site_groups_by_db_connection[$i] = new Syncee_Site_Group();
                    $site_group->title   = 'Site group for ' . $site_url;
                    $site_group->save();
                }

                $site_group = $site_groups_by_db_connection[$i];

                // Save/update site to database connection
                $site->ee_site_id    = 1;
                $site->site_group_id = $site_group->getPrimaryKeyValues(true);

                if ($site->isLocal()) {
                    $site->requests_from_remote_sites_enabled = true;
                }

                $site->save();

                $j += 1;
            }

            $seed_data_files = isset($this->_seed_data_files[$current_test_method_to_be_run])
                ? $this->_seed_data_files[$current_test_method_to_be_run]
                : $this->_seed_data_files
            ;

            foreach ($seed_data_files as $seed_data_file) {
                if (is_scalar($seed_data_file)) {
                    require SYNCEE_PATH_TESTS . '/seeds/' . strtolower($seed_data_file) . '.php';
                }
            }

            $i += 1;
        }

        $this->_switchToDatabaseBasedOnNumber(1);
    }

    private function _installFreshDatabases()
    {
        $db = ee()->db;

        $this->_original_db_name = $db->database;
        $sql_pathname            = $this->_getSqlPathname();

        $syncee_upd              = new Syncee_Upd();

        $i = 1;

        // execute SQL dump on test databases
        while ($db_name = $this->_fetchFromConfig("database.connection.db$i", false)) {
            $this->_switchToDatabaseBasedOnNumber($i);

            $tables = array_map(function ($row) {
                return reset($row);
            }, ee()->db->query('SHOW TABLES')->result_array());

            $is_empty_database_on_start = !count($tables);

            if (!$is_empty_database_on_start) {
                $exp_sites_count = ee()->db->select('site_id')
                    ->from('sites')
                    ->get()
                    ->num_rows()
                ;
            }

            $need_to_truncate_all_tables = $this->_truncation_setup_type === self::TRUNCATE_ALL;

            $need_to_execute_dump = (
                !count($tables) ||
                (isset($exp_sites_count) && !$exp_sites_count) ||
                $need_to_truncate_all_tables
            );

            if ($need_to_truncate_all_tables) {
                foreach ($tables as $table) {
                    ee()->db->truncate($table);
                }
            }

            // install fresh dump
            if ($need_to_execute_dump) {
                $response = shell_exec("mysql56 -u {$db->username} {$db->database} < '$sql_pathname'");
            }

            // uninstall and reinstall syncee
            $syncee_upd->uninstall();

            $syncee_upd->install();

            $i += 1;
        }

        if ($i <= 1) {
            $this->fail('Must have at least two databases in config; found only ' . $i);
        }

        $this->_switchToDatabaseBasedOnNumber();
    }

    private function _truncateTables()
    {
        $syncee_upd = new Syncee_Upd();

        $i = 1;

        while ($db_name = $this->_fetchFromConfig("database.connection.db$i", false)) {
            $this->_switchToDatabaseBasedOnNumber($i);

            $tables = array_map(function ($row) {
                return reset($row);
            }, ee()->db->query('SHOW TABLES')->result_array());

            if ($this->_truncation_setup_type !== self::TRUNCATE_SYNCEE_ONLY) {
                ee()->db->db_debug = false; // db is doing some weird stuff saying that table doesn't exist when it clearly does... let's just get this show on the road
                foreach ($tables as $table) {
                    ee()->db->truncate($table);
                }
                ee()->db->db_debug = true;
            }

            $syncee_upd->uninstall();

            $i += 1;
        }
    }
}