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
 * Simple cache interface
 */
interface ICache {

  /**
   * Read a value from cache
   *
   * @param string $key Cache key
   * @param mixed $default
   * @return mixed Cached value
   */
  public function read($key, $default = null);

  /**
   * Write a value to the cache
   *
   * @param string $key Cache key
   * @param mixed $value Value
   */
  public function write($key, $value);

  /**
   * Clear cache data
   */
  public function clear();
}