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
    private $_rsa_crypt;

    public function __construct()
    {
        $this->_rsa_crypt = new Crypt_RSA();
    }

    public function getPrivateKey()
    {
        if (!$this->private_key) {
            if ($this->public_key) {
                $this->private_key = file_get_contents($this->_getPrivateKeyPathname());
            } else {
                $this->_createKey();
            }
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
        return $this->_rsa_crypt;
    }

    private function _createKey()
    {
        list($this->private_key, $this->public_key, $partial_key) = array_values($this->_rsa_crypt->createKey());
        $this->_writePrivateKeyToFile();
    }

    private function _writePrivateKeyToFile()
    {
        file_put_contents($this->_getPrivateKeyPathname(), $this->private_key);
    }

    private function _getPrivateKeyPathname()
    {
        $syncee_upd           = new Syncee_Upd();
        $private_key_path     = $syncee_upd->getPrivateKeyPath();
        $private_key_basename = md5($this->public_key) . '.txt';

        return $private_key_path . '/' . $private_key_basename;
    }

//    public function __call($method, $args)
//    {
//        if (method_exists($this->_rsa_crypt, $method)) {
//            return call_user_func_array(array($this->_rsa_crypt, $method), $args);
//        }
//    }
}