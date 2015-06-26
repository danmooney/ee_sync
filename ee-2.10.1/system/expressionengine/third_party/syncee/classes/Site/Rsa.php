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

class Syncee_Site_Rsa
{
    public $private_key;

    public $public_key;

    /**
     * @var Crypt_RSA
     */
    private $_crypt;

    public function __construct()
    {
        $this->_crypt = new Crypt_RSA();
    }

    public function getPrivateKey()
    {
        if (!$this->public_key) { // private key could be null if site is remote.  check if public key exists and if not then create both public/private
            $this->_createKey();
        }

        return $this->private_key;
    }

    public function getPublicKey($create_new_key_if_non_existent = true)
    {
        if (!$this->public_key && $create_new_key_if_non_existent) {
            $this->_createKey();
        }

        return $this->public_key;
    }

    public function getCrypt()
    {
        return $this->_crypt;
    }

    private function _createKey()
    {
        list($this->private_key, $this->public_key, $partial_key) = array_values($this->_crypt->createKey());
    }
}