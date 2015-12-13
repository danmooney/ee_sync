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

abstract class Syncee_ActiveRecord_Abstract implements Syncee_Entity_Interface, Syncee_UniqueIdentifier_Interface
{
    const TABLE_NAME = '';

    /**
     * @var string
     */
    protected $_collection_model = 'Syncee_Collection_Generic';

    protected $_is_empty_row = false;

    protected $_is_new = true;

    /**
     * @var array
     */
    protected static $_cols;

    protected $_col_val_mapping = array();

    protected $_non_col_val_mapping = array();

    protected $_primary_key_names = array();

    protected $_has_many_maps = array();

    protected $_has_many_maps_join_types = array();

    protected $_belongs_to;

    /**
     * @param $paginator Syncee_Paginator
     * @return Syncee_Collection_Abstract
     */
    public static function findAll(Syncee_Paginator $paginator = null, $get_map_columns = false)
    {
        ee()->db->select(static::TABLE_NAME . '.*')->from(static::TABLE_NAME);

        if ($paginator) {
            $paginator->modifyQueryOnDriver(ee()->db);
        }

        $empty_row        = new static();
        $collection_model = $empty_row->getCollectionModel();

        if ($get_map_columns) {
            $has_many_maps            = $empty_row->getHasManyMaps();
            $has_many_maps_join_types = $empty_row->getHasManyMapsJoinTypes();

            foreach ($has_many_maps as $has_many_map) {
                $empty_map_row = new $has_many_map();

                $empty_row_table_name     = $empty_row::TABLE_NAME;
                $empty_map_row_table_name = $empty_map_row::TABLE_NAME;

                $join_type                = isset($has_many_maps_join_types[$has_many_map]) ? $has_many_maps_join_types[$has_many_map] : null;

                foreach ($empty_map_row->getPrimaryKeyNames() as $primary_key_name) {
                    // if primary key name already is in the main table, then prevent selection of the map column name as well
                    if ($primary_key_name_already_in_main_table = in_array($primary_key_name, $empty_row->getPrimaryKeyNames())) {
                        continue;
                    }

                    ee()->db->select($empty_map_row_table_name . '.' . $primary_key_name);
                }

                ee()->db->join(
                    $empty_map_row_table_name,
                    "{$empty_row_table_name}.{$empty_row->getPrimaryKeyNames(true)} = {$empty_map_row_table_name}.{$empty_row->getPrimaryKeyNames(true)}",
                    $join_type
                );
            }
        }

        $rows = ee()->db->get()->result_array();

        return new $collection_model($rows);
    }

    /**
     * @param array $conditions
     * @param Syncee_Paginator $paginator
     * @return Syncee_Collection_Abstract
     * @throws Syncee_Exception
     */
    public static function findAllByCondition(array $conditions, Syncee_Paginator $paginator = null)
    {
        ee()->db->select(static::TABLE_NAME . '.*')->from(static::TABLE_NAME);

        /**
         * @var $empty_map_row Syncee_ActiveRecord_Abstract
         */
        $empty_row                = new static();
        $has_many_maps            = $empty_row->getHasManyMaps();
        $has_many_maps_join_types = $empty_row->getHasManyMapsJoinTypes();

        foreach ($conditions as $column => $value) {
            $condition_able_to_be_employed = false;

            if (is_numeric($column)) {
                ee()->db->where($value);
                $condition_able_to_be_employed = true;
            } else {
                if (!in_array($column, static::$_cols) && count($has_many_maps)) {
                    foreach ($has_many_maps as $has_many_map) {
                        $empty_map_row = new $has_many_map();

                        if (!$empty_map_row->hasColumn($column)) {
                            continue;
                        }

                        $empty_row_table_name     = $empty_row::TABLE_NAME;
                        $empty_map_row_table_name = $empty_map_row::TABLE_NAME;

                        $join_type                = isset($has_many_maps_join_types[$has_many_map]) ? $has_many_maps_join_types[$has_many_map] : null;

                        ee()->db->join(
                            $empty_map_row_table_name,
                            "{$empty_row_table_name}.{$empty_row->getPrimaryKeyNames(true)} = {$empty_map_row_table_name}.{$empty_row->getPrimaryKeyNames(true)}",
                            $join_type
                        );

                        $condition_able_to_be_employed = true;
                    }
                } else {
                    $condition_able_to_be_employed = true;
                }

                if (is_array($value)) {
                    ee()->db->where_in($column, $value);
                } else {
                    ee()->db->where($column, $value);
                }
            }

            if (!$condition_able_to_be_employed) {
                throw new Syncee_Exception("Column $column was not able to be used in " . __METHOD__);
            }
        }

        if ($paginator) {
            $paginator->modifyQueryOnDriver(ee()->db);
        }

        $rows             = ee()->db->get()->result_array();

        /**
         * @var $collection_model Syncee_Collection_Abstract
         */
        $collection_model = $empty_row->getCollectionModel();

        if ($is_generic = !$collection_model->getRowModel()) {
            $collection_model->setRowModel($empty_row);
        }

        foreach ($rows as $row) {
            $collection_model->appendToCollectionAsArray($row);
        }

        return $collection_model;
    }

    /**
     * @param $primary_key_value
     * @return Syncee_ActiveRecord_Abstract|static
     */
    public static function findByPk($primary_key_value)
    {
        /**
         * @var $empty_row Syncee_ActiveRecord_Abstract
         */
        $empty_row  = new static();

        if (!$primary_key_value) {
            return $empty_row;
        }

        $primary_keys_on_row  = (array) $empty_row->getPrimaryKeyNames();

        ee()->db->select('*')->from(static::TABLE_NAME);

        if (count($primary_keys_on_row) === 1) {
            $primary_key       = reset($primary_keys_on_row);
            $primary_key_value = is_array($primary_key_value) ? reset($primary_key_value) : $primary_key_value;
            ee()->db->where($primary_key, $primary_key_value);
        } else {
            $primary_key_value = (array) $primary_key_value;
            foreach ($primary_key_value as $key => $value) {
                if (is_numeric($key)) {
                    if (!isset($primary_keys_on_row[$key])) {
                        break;
                    }

                    ee()->db->where($primary_keys_on_row[$key], $primary_key_value[$key]);
                } else {
                    if (!in_array($key, $primary_keys_on_row)) {
                        continue;
                    }

                    ee()->db->where($key, $value);
                }
            }
        }

        $row    = (array) ee()->db->get()->row();
        $is_new = empty($row);

        return new static($row, $is_new);
    }

    public function __construct(array $row = array(), $is_new = true)
    {
        if (!isset(static::$_cols)) {
            static::$_cols = ee()->db->list_fields(static::TABLE_NAME);
        }

        $this->assign($row);

        if (empty($row)) {
            $this->_is_empty_row = true;
        }

        $this->_is_new = $is_new;
    }

    /**
     * Assign data to active record model via enumeration
     * @param array $row
     * @return self
     */
    public function assign(array $row)
    {
        // TODO - this has to be reworked now that public properties representing columns have been removed
        $object_properties = get_object_vars($this);

        // enumerate through row and assign
        foreach ($row as $key => $val) {
            if (@!is_scalar(unserialize($val))) {
                $val = unserialize($val);
            }

            $this->$key = $val;

            // assign values in row to nested objects if properties are defined
            foreach ($object_properties as $object_key => $possible_nested_object) {
                if (!is_object($possible_nested_object)) {
                    continue;
                }

                $nested_object                   = $possible_nested_object;
                $nested_object_object_properties = get_object_vars($nested_object);

                if (!array_key_exists($key, $nested_object_object_properties)) {
                    continue;
                }

                $this->$object_key->$key = $val;

                if (in_array($key, static::$_cols)) {
                    $this->_col_val_mapping[$key]     =& $this->$object_key->$key;
                } else {
                    $this->_non_col_val_mapping[$key] =& $this->$object_key->$key;
                }
            }
        }

        // enumerate through object properties of $this and assign
        foreach ($object_properties as $object_key => $possible_nested_object) {
            if (is_object($possible_nested_object)) {
                $nested_object                   = $possible_nested_object;
                $nested_object_object_properties = get_object_vars($nested_object);

                foreach (static::$_cols as $col) {
                    if (!array_key_exists($col, $nested_object_object_properties)) {
                        continue;
                    }

                    if (in_array($col, static::$_cols)) {
                        $this->$object_key->$col =& $this->_col_val_mapping[$col];
                    } else {
                        $this->$object_key->$col =& $this->_non_col_val_mapping[$col];
                    }
                }
            }
        }

        return $this;
    }

    public function hasColumn($column)
    {
        return in_array($column, static::$_cols);
    }

    public function isEmptyRow()
    {
        return $this->_is_empty_row;
    }

    public function getPrimaryKeyNames($return_scalar = false)
    {
        return $return_scalar
            ? reset($this->_primary_key_names)
            : $this->_primary_key_names
        ;
    }

    public function getPrimaryKeyValues($return_scalar = false)
    {
        $primary_key_values = array();

        foreach ($this->_primary_key_names as $primary_key_name) {
            $primary_key_values[] = array_key_exists($primary_key_name, $this->_col_val_mapping) ? $this->_col_val_mapping[$primary_key_name] : null;
        }

        return $return_scalar
            ? reset($primary_key_values)
            : $primary_key_values
        ;
    }

    public function getPrimaryKeyNamesValuesMap()
    {
        return array_combine($this->getPrimaryKeyNames(), $this->getPrimaryKeyValues());
    }

    public function getUniqueIdentifierKey()
    {
        return $this->getPrimaryKeyNames();
    }

    public function getUniqueIdentifierValue()
    {
        return $this->getPrimaryKeyValues();
    }

    /**
     * @return Syncee_Collection_Abstract
     */
    public function getCollectionModel()
    {
        return new $this->_collection_model();
    }

    public function getBelongsTo()
    {
        return $this->_belongs_to;
    }

    /**
     * @return array
     */
    public function getHasManyMaps()
    {
        return $this->_has_many_maps;
    }

    public function getHasManyMapsJoinTypes()
    {
        return $this->_has_many_maps_join_types;
    }

    // TODO - perhaps assign $this->_is_new to the return value???
    public function isNew()
    {
        $primary_key_values        = array();
        $missing_primary_key_value = false;

        foreach ($this->_primary_key_names as $primary_key_name) {
            if (!array_key_exists($primary_key_name, $this->_col_val_mapping)) {
                $missing_primary_key_value = true;
                break;
            }

            $primary_key_values[$primary_key_name] = $this->$primary_key_name;
        }

        if ($missing_primary_key_value) {
            return true;
        }

        $primary_key_values = array_filter($primary_key_values, function ($primary_key_value) {
            return $primary_key_value !== null;
        });

        if (count($primary_key_values) !== count($this->_primary_key_names)) {
            return false;
        }

        $row = static::findByPk($primary_key_values);

        return $row->isEmptyRow();
    }

    public function save()
    {
        $row = $this->toArray(true);

        if (in_array('create_datetime', static::$_cols) && !isset($row['create_datetime'])) {
            $row['create_datetime'] = gmdate('Y-m-d H:i:s');
        }

        foreach ($row as $key => $val) {
            if (!is_scalar($val) && null !== $val) {
                $row[$key] = serialize($val);
            }
        }

        if ($this->isNew()) {
            $success = ee()->db->insert(static::TABLE_NAME, $row);
            if ($success) {
                // save primary key on this object
                // TODO - test to see if ee()->db->insert_id() returns compound primary key values
                $insert_id = ee()->db->insert_id();

                if ($insert_id) {
                    $insert_id = (array) $insert_id;
                    foreach ($this->_primary_key_names as $idx => $primary_key_name) {
                        $this->$primary_key_name = $insert_id[$idx];
                    }
                }


                $this->_is_new = false;
            }
        } else {
            $where = array();

            foreach ($this->_primary_key_names as $primary_key) {
                $where[$primary_key] = $this->$primary_key;
            }

            $success = ee()->db->update(static::TABLE_NAME, $row, $where);
        }


        $this->_handleManyMapReferences(__FUNCTION__);

        return $success;
    }

    public function delete($where = array(), $handle_many_map_references = true)
    {
        if ($this->_is_new) {
            return false;
        }

        if (!empty($where)) {
            $where_with_existing_column_set_only = array();

            foreach ($where as $key => $value) {
                if (in_array($key, static::$_cols)) {
                    $where_with_existing_column_set_only[$key] = $value;
                }
            }

            $where = $where_with_existing_column_set_only;
        } else {
            foreach ($this->_primary_key_names as $primary_key) {
                $where[$primary_key] = $this->$primary_key;
            }
        }

        $where = array_filter($where, function ($where_value) {
            return $where_value !== null;
        });

        $success = count($where) ? ee()->db->delete(static::TABLE_NAME, $where) : false;

        if ($success) {
            $this->_is_new = true;
        }

        if ($handle_many_map_references) {
            $this->_handleManyMapReferences(__FUNCTION__);
        }

        return $success;
    }

    public function toArray($table_data_only = true)
    {
        $table_cols = static::$_cols;
        $row        = array();

        foreach ($table_cols as $table_property) {
            if (array_key_exists($table_property, $this->_col_val_mapping)) {
                $row[$table_property] = $this->_col_val_mapping[$table_property];
            }
        }

        if (!$table_data_only) {
            $row = array_merge($row, $this->_non_col_val_mapping);
        }

        return $row;
    }

    public function getUniqueIdentifier()
    {
        $identifiers = array();

        foreach ($this->_primary_key_names as $key) {
            $identifiers[] = $this->$key;
        }

        return implode('|', $identifiers);
    }

    public function __set($property, $value)
    {
        if (!in_array($property, static::$_cols)) {
            $this->_non_col_val_mapping[$property] = $value;
        } else {
            $this->_col_val_mapping[$property] = $value;
            $this->_is_empty_row               = false;
        }

        return $this;
    }

    public function __get($property)
    {
        if (array_key_exists($property, $this->_col_val_mapping)) {
            return $this->_col_val_mapping[$property];
        } elseif (array_key_exists($property, $this->_non_col_val_mapping)) {
            return $this->_non_col_val_mapping[$property];
        } elseif (isset($this->$property)) {
            return $this->$property;
        } else {
            return null;
        }
    }

    /**
     * Perform action on map model if it exists
     * @param string $action
     */
    protected function _handleManyMapReferences($action)
    {
        if (!($has_many_maps = $this->_has_many_maps)) {
            return;
        }

        foreach ($has_many_maps as $has_many_map) {
            /**
             * @var $map_model Syncee_ActiveRecord_Abstract
             */
            $map_model                                  = new $has_many_map(array(), false);
            $compound_key_values_missing_in_map_model   = false;
            $compound_key_value_array                   = null;

            // determine if there is a compound key value that is an array
            foreach ($map_model->getPrimaryKeyNames() as $primary_key_name) {
                if (is_array($this->$primary_key_name)) {
                    $compound_key_value_array = $this->$primary_key_name;
                    break;
                }
            }

            $one_of_the_compound_key_values_is_an_array = is_array($compound_key_value_array);

            if ($one_of_the_compound_key_values_is_an_array) {
                foreach ($compound_key_value_array as $compound_key_value_array_idx => $primary_key_value) {
                    $map_model = new $has_many_map(array(), false);

                    foreach ($map_model->getPrimaryKeyNames() as $primary_key_name) {
                        if (is_array($this->$primary_key_name)) {
                            $map_model->$primary_key_name = $compound_key_value_array[$compound_key_value_array_idx];
                        } else {
                            $map_model->$primary_key_name = $this->$primary_key_name;
                        }
                    }

                    $map_model->$action();
                }
            } else {
                foreach ($map_model->getPrimaryKeyNames() as $primary_key_name) {
                    if (strlen($this->$primary_key_name)) {
                        $primary_key_value = $this->$primary_key_name;
                    } else {
                        $compound_key_values_missing_in_map_model = true;
                        continue;
                    }

                    $map_model->$primary_key_name = $primary_key_value;
                }

                if (!$compound_key_values_missing_in_map_model || $action === 'delete') {
                    $map_model->$action($this->getPrimaryKeyNamesValuesMap());
                }
            }
        }
    }
}