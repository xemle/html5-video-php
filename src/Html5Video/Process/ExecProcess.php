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
 * @package       Html5Video\Process
 * @since         html5-video-php v 1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Html5Video\Process;

/**
 * Use php's exec() function to run command
 */
class ExecProcess implements IProcess {

  /**
   * Escape given argument array
   * 
   * @param array $args
   * @return array Escaped arguments
   */
  private function escapeArgs(&$args) {
    $escaped = array();
    foreach ($args as $arg) {
      $escaped[] = escapeshellarg($arg);
    }
    return $escaped;
  }
  
  public function run($bin, $args, &$output) {
    $cmd = $bin . ' ' . join(' ', $this->escapeArgs($args));

    if (PATH_SEPARATOR != '\\') {
      $cmd .= ' 2>&1';
    }
    
    exec($cmd, $output, $return_var);    
    return $return_var;
  }

}
