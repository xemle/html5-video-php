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

interface IProcess {

  /**
   * Run given binary with given arguments. Output lines of stdout and strerr 
   * are added to $output array
   * 
   * @param string $bin Executeable
   * @param array $args Arguments
   * @param array $output Reference to output array
   * @return Return code of execution
   */
  public function run($bin, $args, &$output);
}