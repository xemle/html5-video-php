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

class ProcessMock implements \Html5Video\Process\IProcess {

  var $outputs;

  function __construct($outputs = array()) {
    $this->outputs = $outputs;
  }

  public function getKey($bin, $args) {
    return $bin . ' ' . join(' ', $args);
  }

  public function run($bin, $args, &$output) {
    $key = $this->getKey($bin, $args);
    if (isset($this->outputs[$key])) {
      $lines = (array) $this->outputs[$key];
      foreach ($lines as $line) {
        $output[] = $line;
      }
      return 0;
    }
    return -1;
  }

}