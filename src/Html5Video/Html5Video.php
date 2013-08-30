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

/**
 * Html5 Video converter using ffmpeg to create compatible video for common
 * mobile and desktop devices.
 */
class Html5Video {

  private $Cache;
  private $Process;
  private $defaults = array(
      /**
       * Binary of ffmpeg
       */
      'ffmpeg.bin' => 'ffmpeg',
      /**
       * Binary of qt-faststart
       */
      'qt-faststart.bin' => 'qt-faststart',
      /**
       * List of profile directories
       */
      'profile.dirs' => array(),
      /**
       * Additional video container formats. Array with 'videoEncoder' and
       * 'audioEncoder' settings. Eg
       *
       * 'videoContainers' => array('flv' => array('videoEncoder' => 'flv', 'audioEncoder' => 'mp3'));
       */
      'videoContainers' => array(
        'mp4' => array('videoEncoder' => array('x264', 'h264'), 'audioEncoder' => 'aac'),
        'webm' => array('videoEncoder' => array('vpx', 'vp8'), 'audioEncoder' => 'vorbis'),
        'ogg' => array('videoEncoder' => 'theora', 'audioEncoder' => 'vorbis')
        ),
      /**
       * Converting videos is time consuming. Disable time limit if set
       * to 0. Otherwise set timelimit in seconds. Default is 0 (disabled)
       */
      'timelimit' => 0
  );
  private $config;

  /**
   * Constructor
   *
   * @param array $config Config array
   * @param object $process Process object to call external ffmpeg process
   * @param object $cache Cache object to store ffmpeg settings
   */
  function __construct($config = array(), $process = null, $cache = null) {
    $this->config = array_merge($this->defaults, (array) $config);
    $this->config['profile.dirs'] = (array) $this->config['profile.dirs'];
    $this->config['profile.dirs'][] = __DIR__ . DIRECTORY_SEPARATOR . 'Profiles';

    if ($process) {
      $this->Process = $process;
    } else {
      $this->Process = new Process\ExecProcess();
    }
    if ($cache) {
      $this->Cache = $cache;
    } else {
      $this->Cache = new Cache\MemoryCache();
    }
  }

  /**
   * Extract encoder names from output lines
   *
   * @param array $lines ffmpeg output lines
   * @return array List of supported encoders
   */
  protected function parseEncoder($lines) {
    $encoders = array();
    foreach ($lines as $line) {
      if (preg_match('/^\s+([A-Z .]+)\s+(\w{2,})\s+(.*)$/', $line, $m)) {
        $type = trim($m[1]);
        if (strpos($type, 'E') !== false) {
          $encoder = trim($m[2]);
          if (strpos($encoder, ',') !== false) {
            foreach (split(',', $encoder) as $e) {
              $encoders[] = $e;
            }
          } else {
            $encoders[] = $encoder;
          }
        }
      }
    }

    sort($encoders);
    return $encoders;
  }

  /**
   * Parse streaming informations
   *
   * @param array $lines
   * @return array
   */
  protected function parseInfo($lines) {
    $result = array('videoStreams' => 0, 'audioStreams' => 0);
    foreach ($lines as $line) {
      if (preg_match('/Duration:\s+(\d\d):(\d\d):(\d\d\.\d+)/', $line, $m)) {
        $result['duration'] = $m[1] * 3600 + $m[2] * 60 + $m[3];
      } else if (preg_match('/\s+Stream #(\d)+.*$/', $line, $sm)) {
        if (strpos($line, 'Video:')) {
          $result['videoStreams']++;
          $words = preg_split('/,?\s+/', trim($line));
          for ($i = 0; $i < count($words); $i++) {
            if (preg_match('/(\d+)x(\d+)/', $words[$i], $m)) {
              $result['width'] = $m[1];
              $result['height'] = $m[2];
            }
          }
        } else if (strpos($line, 'Audio:')) {
          $result['audioStreams']++;
        }
      }
    }
    return $result;
  }

  /**
   * Compare two versions
   *
   * @param array $version Version array
   * @param array $other Version array
   * @return boolean True if first version is greater or equal to other version
   */
  protected function isVersionIsGreaterOrEqual($version, $other) {
    $max = min(count($version), count($other));
    for ($i = 0; $i < $max; $i++) {
      if ($version[$i] < $other[$i]) {
        return false;
      }
    }
    return true;
  }

  /**
   * Search for matching encoder
   *
   * @param string|array $needle Encoder type
   * @return mixed
   */
  protected function searchEncoder($needle) {
    if (is_array($needle)) {
      foreach ($needle as $n) {
        $result = $this->searchEncoder($n);
        if ($result) {
          return $result;
        }
      }
      return false;
    }

    $encoders = $this->getEncoders();
    foreach ($encoders as $encoder) {
      if (strpos($encoder, $needle) !== false) {
        return $encoder;
      }
    }
    return false;
  }

  /**
   * Get current ffmpeg driver
   *
   * @return object FfmpegDriver
   */
  protected function getDriver() {
    $version = $this->getVersion();
    if ($this->isVersionIsGreaterOrEqual($version, array(0, 11, 0))) {
      return new Driver\FfmpegDriver();
    } else if ($this->isVersionIsGreaterOrEqual($version, array(0, 9, 0))) {
      return new Driver\FfmpegDriver10();
    } else if ($this->isVersionIsGreaterOrEqual($version, array(0, 8, 0))) {
      return new Driver\FfmpegDriver08();
    } else {
      return new Driver\FfmpegDriver06();
    }
  }

  /**
   * Create a video convert for given profile and target container
   *
   * @param string $targetFormat Target container format
   * @param string $profileName Profile name
   * @return \Html5Video\Converter\Mp4Converter|\Html5Video\Converter\GenericConverter
   * @throws \Exception
   */
  protected function createConverter($targetFormat, $profileName) {
    $profile = $this->getProfile($profileName);

    $videoContainers = $this->config['videoContainers'];
    if (!isset($videoContainers[$targetFormat])) {
      throw new \Exception("Unsupported target video container");
    }
    $targetConfig = $videoContainers[$targetFormat];
    if (!isset($targetConfig['videoEncoder']) || !isset($targetConfig['audioEncoder'])) {
      throw new \Exception("Video or audio encoder are missing for target format $targetFormat");
    }

    $videoEncoder = $this->searchEncoder($targetConfig['videoEncoder']);
    if (!$videoEncoder) {
      throw new \Exception("Video encoder not found for video codec {$targetConfig['videoEncoder']} for $targetFormat");
    }
    $audioEncoder = $this->searchEncoder($targetConfig['audioEncoder']);
    if (!$audioEncoder) {
      throw new \Exception("Audio encoder not found for audio codec {$targetConfig['audioEncoder']} for $targetFormat");
    }

    if ($targetFormat == 'mp4') {
      return new Converter\Mp4Converter($this->Process, $this->getDriver(), $this->config, $profile, $videoEncoder, $audioEncoder);
    }
    return new Converter\GenericConverter($this->Process, $this->getDriver(), $this->config, $profile, $videoEncoder, $audioEncoder);
  }

  /**
   * If no width and height are given in the option read the video
   * file and set width and height from the souce
   *
   * @param string $src Source video filename
   * @param string $dst Desitination video filename
   * @param array $options Convert options
   */
  protected function mergeOptions($src, $dst, &$options) {
    if (!isset($options['width']) && !isset($options['height'])) {
      $info = $this->getVideoInfo($src);
      $options['width'] = $info['width'];
      $options['height'] = $info['height'];
      if (!$info['audioStreams']) {
        $options['audio'] = false;
      }
    }
    if (!isset($options['targetFormat'])) {
      $ext = strtolower(substr($dst, strrpos($dst, '.') + 1));
      $options['targetFormat'] = $ext;
    }
  }

  /**
   * Get current version of ffmpeg
   *
   * @return array Version array(major, minor, patch)
   */
  public function getVersion() {
    $version = $this->Cache->read('version', null);
    if ($version !== null) {
      return $version;
    }
    $version = array(2, 0, 0);

    $lines = array();
    $result = $this->Process->run($this->config['ffmpeg.bin'], array('-version'), $lines);
    if ($result == 0 && $lines && preg_match('/^\w+\s(version\s)?(\d+)\.(\d+)\.(\d+).*/', $lines[0], $m)) {
      $version = array($m[2], $m[3], $m[4]);
    } else if ($result == 0 && $lines && preg_match('/^\w+\s(version\s)?\S*N-(\d+)-.*/', $lines[0], $m)) {
      $winVersion = $m[2];
      if ($winVersion <= 30610) {
        $version = array(0, 6, 0);
      } else if ($winVersion <= 30956) {
        $version = array(0, 7, 0);
      } else if ($winVersion <= 48409) {
        $version = array(0, 8, 0);
      } else if ($winVersion <= 48610) {
        $version = array(1, 0, 1);
      } else if ($winVersion <= 49044) {
        $version = array(1, 1, 0);
      } else {
        $version = array(2, 0, 0);
      }
    }
    $this->Cache->write('version', $version);
    return $version;
  }

  /**
   * Get supported encoder names
   *
   * @return array Sorted list of supported encoders
   */
  public function getEncoders() {
    $encoders = $this->Cache->read('encoders');
    if ($encoders !== null) {
      return $encoders;
    }

    $args = array('-codecs');
    if (!$this->isVersionIsGreaterOrEqual($this->getVersion(), array(0, 8))) {
      $args = array('-formats');
    }
    $lines = array();
    $errCode = $this->Process->run($this->config['ffmpeg.bin'], $args, $lines);
    if (!count($lines) || $errCode != 0) {
      return array();
    }

    $encoders = $this->parseEncoder($lines);
    $this->Cache->write('encoders', $encoders);
    return $encoders;
  }

  /**
   * Read the video profile in given profile directories
   *
   * @param string $name Profile name
   * @return object Profile
   * @throws Exception
   */
  public function getProfile($name) {
    $dirs = $this->config['profile.dirs'];
    foreach ($dirs as $dir) {
      if (!is_dir($dir) || !is_readable($dir)) {
        continue;
      }
      $filename = $dir . DIRECTORY_SEPARATOR . $name . '.profile';
      if (is_readable($filename)) {
        $content = file_get_contents($filename);
        $json = json_decode($content);
        return $json;
      }
    }
    throw new \Exception("Profile $name not found");
  }

  /**
   * List all available profiles
   *
   * @return array List of available profiles
   */
  public function listProfiles() {
    $dirs = $this->config['profile.dirs'];
    $profiles = array();
    foreach ($dirs as $dir) {
      if (!is_dir($dir) || !is_readable($dir)) {
        continue;
      }
      $files = scandir($dir);
      foreach ($files as $file) {
        if (preg_match('/(.*)\.profile$/', $file, $m)) {
           $profiles[] = $m[1];
        }
      }
    }
    return $profiles;
  }

  /**
   * Get information about a video file
   *
   * @param string $src Video filename
   * @return mixed False on error
   */
  public function getVideoInfo($src) {
    $lines = array();
    if (!is_readable($src)) {
      throw new \Exception("Source file '$src' is not readable");
    }
    $this->Process->run($this->config['ffmpeg.bin'], array('-i', $src), $lines);
    if (count($lines)) {
      return $this->parseInfo($lines);
    }
    return false;
  }

  protected function setTimeLimit() {
    $timeLimit = max(0, intval($this->config['timelimit']));
    set_time_limit($timeLimit);
  }

  /**
   * Convert a given video to html5 video
   *
   * @param string $src Source filename
   * @param string $dst Destination filename
   * @param string $profileName Profile name
   * @param array $options Additional options
   * - targetFormat: target container format. Default extension of $dst
   * - width: Width of source video
   * - height: Height of source video
   * - audio: true | false
   * @return mixed
   */
  public function convert($src, $dst, $profileName, $options = array()) {
    $this->setTimeLimit();
    $this->mergeOptions($src, $dst, $options);
    $converter = $this->createConverter($options['targetFormat'], $profileName);
    $result = $converter->create($src, $dst, $options);
    return $result;
  }

}
