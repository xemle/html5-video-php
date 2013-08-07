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
 * @package       Html5Video\Converter
 * @since         html5-video-php v 1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Html5Video\Converter;

class Mp4Converter extends GenericConverter {

  function __construct($Process, $Driver, $config, $profile, $videoCodec, $audioCodec) {
    parent::__construct($Process, $Driver, $config, $profile, $videoCodec, $audioCodec);
  }

  protected function addVideoArgs(&$options) {
    parent::addVideoArgs($options);

    $this->addOption('-pre:v', 'baseline');
    $this->addOption('-level', 30);
    $this->addOption('-refs', 1);
  }

  /**
   * Move mp4 meta data to the front to support streaming
   *
   * @param string $src Filename of mp4
   * @return int Result. 0 for success
   */
  protected function faststart($src) {
    if (!$this->config['qt-faststart.bin']) {
      return 0;
    }
    $output = array();
    $dst = $src . '.mp4';
    $result = $this->Process->run($this->config['qt-faststart.bin'], array($src, $dst), $output);
    if ($result == 0) {
      unlink($src);
      rename($dst, $src);
    }
    return $result;
  }

  public function create($src, $dst, $options = array()) {
    $result = parent::create($src, $dst, $options);
    if ($result == 0) {
      $result = $this->faststart($dst);
    }
    return $result;
  }

}
