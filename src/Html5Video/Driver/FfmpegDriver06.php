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
 * @package       Html5Video\Driver
 * @since         html5-video-php v 1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Html5Video\Driver;

class FfmpegDriver06 extends FfmpegDriver08 {
  var $map06 = array(
      '-codecs' => '-formats'
  );

  public function mapParam($param) {
    if (isset($this->map06[$param])) {
      return $this->map06[$param];
    }
    return parent::mapParam($param);
  }
}