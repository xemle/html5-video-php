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
 * @since         html5-video-php v 1.0.2
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Html5Video\Cache;

class MemoryCache implements ICache {

  var $data = array();

  function __construct($data = array()) {
    $this->data = $data;
  }

  public function clear() {
    $this->data = array();
    return true;
  }

  public function read($key, $default = null) {
    if (isset($this->data[$key])) {
      return $this->data[$key];
    }
    return $default;
  }

  public function write($key, $value) {
    $this->data[$key] = $value;
    return true;
  }

}
