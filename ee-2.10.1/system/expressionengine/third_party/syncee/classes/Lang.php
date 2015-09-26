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

class Syncee_Lang
{
    private static $_language;

    private static $_default_language = 'english';

    private static $_language_files = array();

    public static function setLanguage($language)
    {
        static::$_language = $language;
        static::_parseLanguageFiles();
    }

    public static function getLanguageFiles()
    {
        if (!static::$_language) {
            static::setLanguage(static::$_default_language);
        }

        return static::$_language_files;
    }

    public static function _($lang_line)
    {
        $language_files                       = static::getLanguageFiles();

        $translation                          = '';
        $translation_exists_in_language_files = isset($language_files[static::$_language], $language_files[static::$_language][$lang_line]);

        if ($translation_exists_in_language_files) {
            $translation = $language_files[static::$_language][$lang_line];
        } elseif (function_exists('lang')) {
            $translation = lang($lang_line);
        }

        return $translation ?: $lang_line;
    }

    public static function resolveTranslationToConstant($translation, $loose = true)
    {
        $resolved_constant = null;

        $language_files = static::getLanguageFiles();

        array_walk_recursive($language_files, function ($translation_to_test, $constant) use ($translation, $loose, &$resolved_constant) {
            if ($resolved_constant) {
                return;
            }

            if ($translation === $translation_to_test) {
                $resolved_constant = $constant;
            }
        });

        return $resolved_constant ?: false;
    }

    public static function resolveTranslationToLanguage($translation, $loose = true)
    {
        // TODO
        $language_files = static::getLanguageFiles();
        return $translation;
    }

    private static function _parseLanguageFiles()
    {
        $language_file_path = SYNCEE_PATH . '/' . 'language';
        $iterator           = new DirectoryIterator($language_file_path);

        foreach ($iterator as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }

            $language = basename($file->getPathname());

            $language_file_basename = basename(SYNCEE_PATH) . '_lang.php';
            $language_file_pathname = $file->getPathname() . '/' . $language_file_basename;

            if (!is_file($language_file_pathname) || !is_readable($language_file_pathname)) {
                continue;
            }

            include $language_file_pathname;

            if (isset($lang) && is_array($lang)) {
                static::$_language_files[$language] = $lang;
            }
        }
    }
}