<?php

require_once dirname(__FILE__) . '/../_init.php';

abstract class Syncee_Unit_Test_Case_Abstract extends Testee_Unit_Test_Case
{
    const TRUNCATE_ALL         = 'TRUNCATE_ALL';
    const TRUNCATE_SYNCEE_ONLY = 'TRUNCATE_SYNCEE_ONLY';

    private $_original_db_name;

    protected $_sql_file;

    protected $_databases;

    protected $_http_host_running_tests;

    protected $_truncation_setup_type = self::TRUNCATE_ALL;

    public function setUp()
    {
        $this->_http_host_running_tests = $_SERVER['HTTP_HOST'];
        $this->_installFreshDatabases();
    }

    public function tearDown()
    {
        $_SERVER['HTTP_HOST'] = $this->_http_host_running_tests;
        $this->_truncateTables();
        $this->_switchToDatabaseBasedOnNumber();
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
        $i = 1;
        while ($db_name = $this->_fetchFromConfig("database.connection.db$i", false)) {
            $this->_switchToDatabaseBasedOnNumber($i);
            $j = 1;
            while ($site_url = $this->_fetchFromConfig("site.url$j", false)) {
                ee()->db->insert(Syncee_Site::TABLE_NAME, array(
                    'site_id' => 1,
                    'site_url' => $site_url
                ));

                $j += 1;
            }

            $i += 1;
        }
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

            if (count($tables)) {
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
                shell_exec("mysql -u {$db->username} -p{$db->password} {$db->database} < '$sql_pathname'");
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
                foreach ($tables as $table) {
                    ee()->db->truncate($table);
                }
            }

            $syncee_upd->uninstall();

            $i += 1;
        }
    }
}