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
    private $_private_key;

    private $_public_key;

    /**
     * @var Crypt_RSA
     */
    private $_rsa_crypt;

    public function __construct()
    {
        $this->_rsa_crypt = new Crypt_RSA();
    }

    public function getPrivateKey()
    {
        if (!$this->_public_key) {
            $this->createKey();
        }

        return $this->_private_key;
    }

    public function getPublicKey()
    {
        if (!$this->_public_key) {
            $this->createKey();
        }

        return $this->_public_key;
    }

    public function getCrypt()
    {
        return $this->_rsa_crypt;
    }

    public function createKey()
    {
        list($this->_private_key, $this->_public_key, $partial_key) = array_values($this->_rsa_crypt->createKey());
        $this->_writePrivateKeyToFile();
    }

    private function _writePrivateKeyToFile()
    {
        $private_key_path = SYNCEE_PATH . '/.private_keys';
        $private_key_basename = md5($this->_public_key) . '.txt';
        file_put_contents($private_key_path . '/' . $private_key_basename, $this->_private_key);
    }

//    public function __call($method, $args)
//    {
//        if (method_exists($this->_rsa_crypt, $method)) {
//            return call_user_func_array(array($this->_rsa_crypt, $method), $args);
//        }
//    }
}