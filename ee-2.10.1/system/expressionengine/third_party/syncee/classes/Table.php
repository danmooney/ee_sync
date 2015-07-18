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

class Syncee_Table
{
    /**
     * @var Syncee_Table_Column_Collection
     */
    private $_column_collection;

    /**
     * @var Syncee_Collection_Abstract
     */
    private $_row_collection;

    public function __construct(Syncee_Table_Column_Collection $column_collection, Syncee_Collection_Abstract $row_collection)
    {
        $this->_column_collection = $column_collection;
        $this->_row_collection    = $row_collection;
    }

    public function __toString()
    {
        $html  = '<table class="collection-table">';
        $html .= '<thead><tr>';

        /**
         * @var $column Syncee_Table_Column
         */
        foreach ($this->_column_collection as $column) {
            // TODO - ordering logic
            $html .= sprintf('<th>%s</th>', $column->getLabel());
        }

        $html .= '</tr></thead>';


        $html .= '<tbody>';

        /**
         * @var $row Syncee_ActiveRecord_Abstract
         * @var $formatter Syncee_Table_Column_Value_Formatter_Abstract
         */
        foreach ($this->_row_collection as $row) {
            $html .= '<tr>';

            foreach ($this->_column_collection as $column) {
                $html .= sprintf('<td align="%s">', $column->getAlign());
                // TODO - put __toString in $column???
                if (is_callable($column->getColumnReferenceValue())) {
                    $callable         = $column->getColumnReferenceValue();
                    $column_contents  = $callable($row);
                } elseif (null === $column->getColumnReferenceValue()) {
                    $column_contents = $column->getLabel();
                } else {
                    $column_contents = $row->{$column->getColumnReferenceValue()};
                }

                if ($formatter = $column->getFormatter()) {
                    $column_contents = $formatter->format($column_contents, $row);
                }

                if (strip_tags($column_contents) === $column_contents) {
                    $column_contents = sprintf('<span>%s</span>', $column_contents);
                }

                $html .= $column_contents;

                $html .= '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
}