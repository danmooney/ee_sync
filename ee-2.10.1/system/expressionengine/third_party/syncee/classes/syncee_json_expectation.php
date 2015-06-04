<?php

require_once dirname(__FILE__) . '/../_init.php';

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