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

class GenericConverter {
  var $Process;
  var $Driver;
  var $config;
  var $profile;
  var $videoEncoder;
  var $audioEncoder;

  var $_args = array();

  function __construct($Process, $Driver, $config, $profile, $videoEncoder, $audioEncoder) {
    $this->Process = $Process;
    $this->Driver = $Driver;
    $this->config = $config;
    $this->profile = $profile;
    $this->videoEncoder = $videoEncoder;
    $this->audioEncoder = $audioEncoder;
  }

  protected function resize($width, $height, $maxWidth, $maxHeight) {
    if ($width > $maxWidth) {
      $height = ($maxWidth / $width) * $height;
      $width = $maxWidth;
    }
    if ($height > $maxHeight) {
      $width = ($maxHeight / $height) * $width;
      $height = $maxHeight;
    }
    $width = ceil($width);
    $height = ceil($height);
    // width and height must be divisible by 2
    if (($width & 1)) {
      $width++;
    }
    if (($height & 1)) {
      $height++;
    }
    return array($width, $height);
  }

  protected function addOption($name, $value) {
    $this->_args[] = $this->Driver->mapParam($name);
    $this->_args[] = $value;
  }

  protected function addVideoArgs(&$options) {
    $this->addOption('-c:v', $this->videoEncoder);
    $this->addOption('-b:v', $this->profile->video->bitrate);

    $size = $this->resize($options['width'], $options['height'], $this->profile->video->width, $this->profile->video->height);
    $this->addOption('-s', join('x', $size));
  }

  protected function addAudioArgs() {
    $this->addOption('-c:a', $this->audioEncoder);
    $this->addOption('-b:a', $this->profile->audio->bitrate);
    $this->addOption('-r:a', $this->profile->audio->samplingrate);
  }

  protected function addAdditionalOptions() {
    $this->addOption('-threads', 8);
  }

  protected function convert($bin, &$args, &$output) {
    $result = $this->Process->run($bin, $args, $output);
    if ($result != 0) {
      throw new \Exception("Could not convert: " . $bin . ' ' . join(' ', $args));
    }
    return $result;
  }

  public function create($src, $dst, $options = array()) {
    $this->_args = array();

    $this->addOption('-i', $src);
    $this->addVideoArgs($options);
    if (!isset($options['audio']) || $options['audio'] !== false) {
      $this->addAudioArgs();
    }
    $this->addAdditionalOptions();
    $this->addOption('-strict', 'experimental');
    $this->_args[] = '-y';
    $this->_args[] = $dst;

    $output = array();
    $result = $this->convert($this->config['ffmpeg.bin'], $this->_args, $output);
    return $result;
  }

}