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

class Syncee_Json_Expectation extends SimpleExpectation
{
    /**
     * Tests the expectation.
     * @param mixed $compare Should be JSON.
     * @return boolean        True on match.
     * @access public
     */
    function test($compare)
    {
        $compare_json_decoded = json_decode($compare);

        return ($compare && $compare_json_decoded != $compare && json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Returns a human readable test message.
     * @param mixed $compare Comparison value.
     * @return string             Description of success
     *                               or failure.
     * @access public
     */
    function testMessage($compare)
    {
        $dumper = $this->getDumper();
        return 'Expected JSON, got [' . $dumper->describeValue($compare) . ']';
    }
}