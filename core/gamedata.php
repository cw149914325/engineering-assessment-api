<?php
/**
 * Class
 * Haypi Inc.,
 */

class GameData {
    private static $datacache = array();

    /**
     * memcache
     */
    public static function getgamedata($varname, $index1 = '', $index2 = '', $index3 = '', $skipphpcache = false) {
        $key = $varname.(($index1!=='')?($index1.'-'):'').(($index2!=='')?($index2.'-'):'').(($index3!=='')?($index3.'-'):'');
        if (!$skipphpcache and array_key_exists($key, self::$datacache)) {
            return self::$datacache[$key];
        }
        $result = Memcachedclass::get($key);
        if ($result!==false && !$skipphpcache) {
            if (!$skipphpcache) {
                self::$datacache[$key] = $result;
            }
            return $result;
        }
        else {
            require(APP_PATH.'gamedata'.DIRECTORY_SEPARATOR.'gamedata.php');
            $array = $$varname;
            $value = ($index1!=='')?(($index2!=='')?(($index3!=='')?((isset($array[$index1][$index2][$index3]))?($array[$index1][$index2][$index3]):(false)):((isset($array[$index1][$index2]))?($array[$index1][$index2]):(false))):((isset($array[$index1]))?($array[$index1]):(false))):((isset($array))?($array):(false));
            if ($value!==false) {
                Memcachedclass::set($key, $value, 0);
                if (!$skipphpcache) {
                    self::$datacache[$key] = $result;
                }
                return $value;
            }
            else {
                log_message('b_gamedata failed: ' . "{$varname},{$index1},{$index2},{$index3}");
            }
        }
    }

    /**
     * shmop
     */
    /*
    public static function getgamedata($varname, $index1 = '', $index2 = '', $index3 = '', $skipphpcache = false) {
        $key = $varname.(($index1!=='')?($index1.'-'):'').(($index2!=='')?($index2.'-'):'').(($index3!=='')?($index3.'-'):'');
        if (!$skipphpcache and array_key_exists($key, self::$datacache)) {
            return self::$datacache[$key];
        }


        $shm_id = shmop_open(0xF, "a", 0644, 150000);
        $shm_size = shmop_size($shm_id);
        $my_string = shmop_read($shm_id, 0, $shm_size);
        shmop_close($shm_id);

        $result = Memcachedclass::get($key);
        if ($result!==false) {
            if (!$skipphpcache) {
                self::$datacache[$key] = $result;
            }
            return $result;
        }
        else {
            require(APP_PATH.'gamedata'.DIRECTORY_SEPARATOR.'gamedata.php');
            $array = $$varname;
            $value = ($index1!=='')?(($index2!=='')?(($index3!=='')?((isset($array[$index1][$index2][$index3]))?($array[$index1][$index2][$index3]):(false)):((isset($array[$index1][$index2]))?($array[$index1][$index2]):(false))):((isset($array[$index1]))?($array[$index1]):(false))):((isset($array))?($array):(false));
            if ($value!==false) {
                Memcachedclass::set($key, $value, 0);
                if (!$skipphpcache) {
                    self::$datacache[$key] = $result;
                }
                return $value;
            }
            else {
                log_message('b_gamedata failed: ' . "{$varname},{$index1},{$index2},{$index3}");
            }
        }
    }
     */

}
?>