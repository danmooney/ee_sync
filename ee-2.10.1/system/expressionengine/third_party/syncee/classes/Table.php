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

    /**
     * @var Syncee_Table_Row_Formatter_Abstract
     */
    private $_table_row_formatter;

    /**
     * @var Syncee_Table_Formatter_Abstract
     */
    private $_table_formatter;


    public function __construct(
        Syncee_Table_Column_Collection $column_collection,
        Syncee_Collection_Abstract $row_collection,
        Syncee_Table_Row_Formatter_Abstract $table_row_formatter = null,
        Syncee_Table_Formatter_Abstract $table_formatter_collection = null)
    {
        $this->_column_collection              = $column_collection;
        $this->_row_collection                 = $row_collection;
        $this->_table_row_formatter = $table_row_formatter;
        $this->_table_formatter     = $table_formatter_collection;
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
            $html .= sprintf(
                '<tr%s>',
                $this->_table_row_formatter
                    ? ' ' . $this->_table_row_formatter->formatBasedOnRow($row)
                    : ''
            );

            foreach ($this->_column_collection as $column) {
                $html .= sprintf('<td align="%s">', $column->getAlign());
                // TODO - put __toString in $column???
                if (is_callable($column->getColumnReferenceValue()) && !is_scalar($column->getColumnReferenceValue())) { // is_callable will return true for global functions; need to check if the reference value isn't scalar in addition to alleviate that issue
                    $callable         = $column->getColumnReferenceValue();
                    $column_contents  = $callable($row);
                } elseif (null === $column->getColumnReferenceValue()) {
                    $column_contents = $column->getLabel();
                } elseif ($row->hasColumn($column->getColumnReferenceValue())) {
                    $column_contents = $row->{$column->getColumnReferenceValue()};
                } else {
                    $column_contents = $column->getColumnReferenceValue();
                }

                if ($formatter = $column->getFormatter()) {
                    $column_contents = $formatter->format($column_contents, $row);
                }

                $column_contents                      = trim($column_contents);
                $column_contents_have_no_html_wrapper = (
                    substr($column_contents, 0, 1) !== '<' ||
                    substr($column_contents, -1) !== '>'
                );

                if ($column_contents_have_no_html_wrapper) {
                    if (!strlen($column_contents)) {
                        $column_contents = '<i>(N/A)</i>';
                    }

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