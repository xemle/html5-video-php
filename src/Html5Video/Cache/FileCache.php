<?php
/**
 * html5-video-php - ffmpeg wrapper for HTML5 videos
 *
 * Copyright (c) 2013 Sebastian Felis <sebastian@phtagr.org>
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Sebastian Felis <sebastian@phtagr.org>
 * @link          http://github.com/xemle/html5-video-php
 * @package       Html5Video\Cache
 * @since         html5-video-php v 1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Html5Video\Cache;

/**
 * Simple file cache
 */
class FileCache implements ICache {
  var $name = 'html5-video.cache';
  var $cache = null;
  var $data = null;

  function __construct($dir) {
    if (is_file($dir) && is_writable($dir)) {
      $this->cache = $dir;
    } else if (is_dir($dir) && is_writable($dir)) {
      $this->cache = $dir . DIRECTORY_SEPARATOR . $this->name;
    } else if (is_writable(dirname(__FILE__))) {
      $this->cache = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name;
    } else {
      $this->cache = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->name;
    }
  }

  private function readFile() {
    $content = file_get_contents($this->cache);
    if ($content) {
      try {
        $this->data = unserialize($content);
      } catch (Exception $e) {
        echo "Could not serialize content: $content";
        $this->data = array();
      }
    } else {
      $this->data = array();
    }
  }

  /**
   * Read given
   *
   * @param string $key Cache key
   * @param mixed $default Cache value
   * @return mixed Cache value
   */
  public function read($key, $default = null) {
    if ($this->data === null) {
      $this->readFile();
    }
    if (isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      return $default;
    }
  }

  /**
   * Write cache data to file
   */
  private function writeFile() {
    $content = serialize($this->data);
    if (!file_put_contents($this->cache, $content)) {
      echo "Could not write content: $content";
    }
  }

  /**
   * Write a cache value
   *
   * @param String $key Cache key
   * @param Mixed $value Cache value
   */
  public function write($key, $value) {
    $this->data[$key] = $value;
    $this->writeFile();
  }

  /**
   * Clear cache data
   */
  public function clear() {
    $this->data = array();
    $this->writeFile();
  }
}
