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
 * @package       Html5Video
 * @since         html5-video-php v 1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Html5Video;

require_once 'Process' . DIRECTORY_SEPARATOR . 'ProcessMock.php';

class Html5VideoTest extends \PHPUnit_Framework_TestCase {
  var $resourceDir;
  var $sysConfig = false;

  protected function setUp() {
    parent::setUp();
    $this->resourceDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Resource' . DIRECTORY_SEPARATOR;
    if (isset($_ENV['ffmpeg.bin']) && isset($_ENV['qt-faststart.bin']) && is_writable($this->resourceDir)) {
      $this->sysConfig = array(
        'ffmpeg.bin' => $_ENV['ffmpeg.bin'],
        'qt-faststart.bin' => $_ENV['qt-faststart.bin']
        );
    }
  }

  protected function createHtml5VideoMock($process = null, $cache = null) {
    if (!$process) {
      $process = new Process\ProcessMock();
    }
    if (!$cache) {
      $cache = new Cache\MemoryCache();
    }
    $html5video = new Html5Video(array(), $process, $cache);
    return $html5video;
  }

  public function testGetVersionWin() {
    $commandMock = new Process\ProcessMock(array('ffmpeg -version' => array('ffmpeg version git-N-30610-g1929807')));
    $html5video = $this->createHtml5VideoMock($commandMock);
    $this->assertEquals(array(0, 6, 0), $html5video->getVersion());

    $commandMock = new Process\ProcessMock(array('ffmpeg -version' => array('ffmpeg version N-30956-g81ef892, Copyright (c) 2000-2011 the FFmpeg developers')));
    $html5video = $this->createHtml5VideoMock($commandMock);
    $this->assertEquals(array(0, 7, 0), $html5video->getVersion());

    $commandMock = new Process\ProcessMock(array('ffmpeg -version' => array('ffmpeg version N-48409-g43adc62')));
    $html5video = $this->createHtml5VideoMock($commandMock);
    $this->assertEquals(array(0, 8, 0), $html5video->getVersion());

    $commandMock = new Process\ProcessMock(array('ffmpeg -version' => array('ffmpeg version N-48610-gb23aff6 Copyright (c) 2000-2013 the FFmpeg developers')));
    $html5video = $this->createHtml5VideoMock($commandMock);
    $this->assertEquals(array(1, 0, 1), $html5video->getVersion());

    $commandMock = new Process\ProcessMock(array('ffmpeg -version' => array('ffmpeg version N-49044-g89afa63')));
    $html5video = $this->createHtml5VideoMock($commandMock);
    $this->assertEquals(array(1, 1, 0), $html5video->getVersion());

    $commandMock = new Process\ProcessMock(array('ffmpeg -version' => array('ffmpeg version N-55721-gc443689')));
    $html5video = $this->createHtml5VideoMock($commandMock);
    $this->assertEquals(array(2, 0, 0), $html5video->getVersion());
  }

  public function testGetVersion() {
    $outputs = array(
        'ffmpeg version 0.8.6-4:0.8.6-0ubuntu0.12.04.1, Copyright (c) 2000-2013 the Libav developers',
        '  built on Apr  2 2013 17:02:36 with gcc 4.6.3'
    );
    $commandMock = new Process\ProcessMock(array('ffmpeg -version' => $outputs));
    $html5video = $this->createHtml5VideoMock($commandMock);

    $version = $html5video->getVersion();
    $this->assertEquals(array(0, 8, 6), $version);
  }

  public function testGetEncodersForVersion086() {
    $codecs_output = <<<'EOT'
Codecs:
 D..... = Decoding supported
 .E.... = Encoding supported
 ..V... = Video codec
 ..A... = Audio codec
 ..S... = Subtitle codec
 ...S.. = Supports draw_horiz_band
 ....D. = Supports direct rendering method 1
 .....T = Supports weird frame truncation
 ------
  EV    libx264         libx264 H.264 / AVC / MPEG-4 AVC / MPEG-4 part 10
 DEA D  aac             Advanced Audio Coding
 D A D  aac_latm        AAC LATM (Advanced Audio Codec LATM syntax)
  EA    libvorbis       libvorbis Vorbis
 DEV    libvpx          libvpx VP8
  EA    libmp3lame      libmp3lame MP3 (MPEG audio layer 3)

Note, the names of encoders and decoders do not always match, so there are
several cases where the above table shows encoder only or decoder only entries
even though both encoding and decoding are supported. For example, the h263
decoder corresponds to the h263 and h263p encoders, for file formats it is even
worse.
EOT;
    $outputs = array(
        'ffmpeg -version' => 'ffmpeg version 0.8.6',
        'ffmpeg -codecs' => preg_split('/\n/', $codecs_output)
    );
    $commandMock = new Process\ProcessMock($outputs);
    $html5video = $this->createHtml5VideoMock($commandMock);

    $encoders = $html5video->getEncoders();
    $this->assertSame(array('aac', 'libmp3lame', 'libvorbis', 'libvpx', 'libx264'), $encoders);
  }

  public function testGetProfile() {
    $html5video = $this->createHtml5VideoMock();
    $profile = $html5video->getProfile('1080p-sd');
    $this->assertNotEmpty($profile);
  }

  public function testListProfiles() {
    $html5video = $this->createHtml5VideoMock();
    $profiles = $html5video->listProfiles('1080p-sd');
    sort($profiles);
    $expected = array('1080p-hd', '1080p-sd', '240p-hd', '240p-sd', '360p-hd', '360p-sd', '480p-hd', '480p-sd', '720p-hd', '720p-sd');
    $this->assertEquals($expected, $profiles);
  }

  public function testGetVideoInfo() {
    $src = __FILE__;

    $codecs_output = <<<'EOT'
ffmpeg version 0.8.6-4:0.8.6-0ubuntu0.12.04.1, Copyright (c) 2000-2013 the Libav developers
  built on Apr  2 2013 17:02:36 with gcc 4.6.3
Input #0, ogg, from 'video.ogg':
  Duration: 00:00:01.34, start: 0.000000, bitrate: 488 kb/s
    Stream #0.0: Video: theora, yuv420p, 1920x1080 [PAR 1:1 DAR 16:9], 30 fps, 30 tbr, 30 tbn, 30 tbc
    Stream #0.1: Audio: flac, 44100 Hz, 2 channels, s16
    Metadata:
      ENCODER         : Lavf53.21.1
At least one output file must be specified
EOT;
    $outputs = array(
        'ffmpeg -version' => 'ffmpeg version 0.8.6',
        "ffmpeg -i $src" => preg_split('/\n/', $codecs_output)
    );
    $commandMock = new Process\ProcessMock($outputs);
    $html5video = $this->createHtml5VideoMock($commandMock);

    $info = $html5video->getVideoInfo($src);
    $this->assertEquals(1, $info['videoStreams']);
    $this->assertEquals(1, $info['audioStreams']);
    $this->assertEquals(1.34, $info['duration']);
    $this->assertEquals(1920, $info['width']);
    $this->assertEquals(1080, $info['height']);
  }

  public function testGetVideoInfoWithoutAudio() {
    $src = __FILE__;

    $codecs_output = <<<'EOT'
ffmpeg version 0.8.6-4:0.8.6-0ubuntu0.12.04.1, Copyright (c) 2000-2013 the Libav developers
  built on Apr  2 2013 17:02:36 with gcc 4.6.3
Input #0, ogg, from 'video-no-audio.ogg':
  Duration: 00:00:01.00, start: 0.000000, bitrate: 593 kb/s
    Stream #0.0: Video: theora, yuv420p, 768x576 [PAR 1:1 DAR 4:3], 25 tbr, 25 tbn, 25 tbc
At least one output file must be specified
EOT;
    $outputs = array(
        'ffmpeg -version' => 'ffmpeg version 0.8.6',
        "ffmpeg -i $src" => preg_split('/\n/', $codecs_output)
    );
    $commandMock = new Process\ProcessMock($outputs);
    $html5video = $this->createHtml5VideoMock($commandMock);

    $info = $html5video->getVideoInfo($src);
    $this->assertEquals(1, $info['videoStreams']);
    $this->assertEquals(0, $info['audioStreams']);
    $this->assertEquals(1, $info['duration']);
    $this->assertEquals(768, $info['width']);
    $this->assertEquals(576, $info['height']);
  }

  public function testConvertMp4() {
    if (!$this->sysConfig) {
      return;
    }

    $profileName = '720p-sd';
    $targetFormat = 'mp4';

    $html5 = new Html5Video($this->sysConfig);
    $dst = $this->resourceDir . 'test-' . $profileName . '.' . $targetFormat;
    $html5->convert($this->resourceDir . 'video.ogg', $dst, $profileName);

    $this->assertTrue(file_exists($dst));
    unlink($dst);
  }

  public function testConvertWebm() {
    if (!$this->sysConfig) {
      return;
    }

    $profileName = '480p-sd';
    $targetFormat = 'webm';

    $html5 = new Html5Video($this->sysConfig);
    $dst = $this->resourceDir . 'test-' . $profileName . '.' . $targetFormat;
    $html5->convert($this->resourceDir . 'video.ogg', $dst, $profileName);

    $this->assertTrue(file_exists($dst));
    unlink($dst);
  }

  public function testConvertOgg() {
    if (!$this->sysConfig) {
      return;
    }

    $profileName = '240p-sd';
    $targetFormat = 'ogg';

    $html5 = new Html5Video($this->sysConfig);
    $dst = $this->resourceDir . 'test-' . $profileName . '.' . $targetFormat;
    $html5->convert($this->resourceDir . 'video-no-audio.ogg', $dst, $profileName);

    $this->assertTrue(file_exists($dst));
    unlink($dst);
  }
}
