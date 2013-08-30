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

class Process implements IProcess {

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

  /**
   * Read stream content to output array
   *
   * @param resource $stream Stream resource
   * @param array $output
   */
  protected function readStream(&$stream, &$output) {
    $content = stream_get_contents($stream);
    if (strlen(rtrim($content))) {
      foreach (preg_split('/\r?\n/', $content) as $line) {
        $output[] = $line;
      }
    }
  }

  public function run($bin, $args, &$output) {
    $pipeDescriptions = array(
      0 => array("pipe", "r"),
      1 => array("pipe", "w"),
      2 => array("pipe", "w")
    );

    $cmd = $bin . ' ' . join(' ', $this->escapeArgs($args));

    $process = proc_open($cmd, $pipeDescriptions, $pipes);

    if (is_resource($process)) {
      stream_set_blocking($pipes[1], 1);
      stream_set_blocking($pipes[2], 1);
      $this->readStream($pipes[1], $output);
      $this->readStream($pipes[2], $output);

      fclose($pipes[0]);
      fclose($pipes[1]);
      fclose($pipes[2]);
      $result = proc_close($process);
    }
    return $result;
  }

}