# Create HTML5 Videos

ffmpeg wrapper to convert videos to HTML5 compatible formats of MP4, WEBM, and
OGG to support all common mobile devices and desktop browsers. Converted
videos can be used with [videojs](http://www.videojs.com) or
[flowplayer](http://http://flowplayer.org).

With different ffmpeg versions, their specific option syntax and different
devices it is tricky to convert videos to HTML5 compatible videos. This wrapper
library makes HTML5 video conversion as easy as possible.

To support most devices and browser MP4, WEBM, and OGG video container are
supported. See [Video on the Web](http://diveintohtml5.info/video.html) for
further details.

To support also Apples iDevices like iPod, iPhone, and iPad following settings
are used for MP4 format (see
[Video Encoding, Flowplayer.org](http://flash.flowplayer.org/plugins/javascript/ipad.html#video-encoding)):

  * Baseline Profile
  * Level 3.0
  * 1 reference frame

Further `qt-faststart` is used to move MP4 meta data from end to the beginning
of the MP4 file to support fast playback while downloading the MP4 video.

## Quickstart

```php
$config = array(
  'ffmpeg.bin' => '/usr/bin/ffmpeg',
  'qt-faststart.bin' => '/usr/bin/qt-faststart',
);
$html5 = new Html5Video\Html5Video($config);

$html5->convert('source.avi', 'html5-720p.mp4', '720p-sd');
```

## Requirements

* [ffmpeg](http://www.ffmpeg.org) with x264 support
* qt-faststart (comes with ffmpeg)

Following ffmpeg versions from 0.6 are supported

### Install ffmpeg on Ubuntu

```
$ sudo apt-get install ffmpeg x264
```

### Windows builds

Download latest ffmpeg from [Zeranoe FFmpeg builds](http://ffmpeg.zeranoe.com/builds)
and qt-faststart from [Windows qt-faststart Builds](http://ffmpeg.zeranoe.com/blog/?p=59)


## Installing

html5-video-php uses [composer](http://getcomposer.org)

```
$ curl -sS https://getcomposer.org/installer | php
```
 

## Usage

Include `html5-video-php` in your `composer.json` file

    {
      "require": {
        "xemle/html5-video-php": "1.0.*"
      }
    }

Create a H264 video

```php
$config = array(
  'ffmpeg.bin' => '/usr/bin/ffmpeg',
  'qt-faststart.bin' => '/usr/bin/qt-faststart',
);
$html5 = new Html5Video\Html5Video($config);

// target format is the file extension of $targetVideo. One of mp4, webm, or ogg
$profileName = '720p-hd'; // other profiles are listed in src/Html5Video/profiles
$html5->convert($srcVideo, $targetVideo, $profileName);
```


## Usage CLI

You can also you the command line interface `bin/Html5VideoConverter.php` to
convert HTML5 videos:

```bash
$ php bin/Html5VideoConverter.php input.avi output.mp4 720p-sd
```


## Profiles

There are different video profiles to create different video sizes:

* 1080p-hd
* 1080p-sd
* 720p-hd
* 720p-sd
* 480p-hd
* 480p-sd
* 360p-hd
* 360p-sd
* 240p-hd
* 240p-sd

hd stands for high definition and sd for standard definition profiles.

The profile are simple json definition files like

```javascript
{
  "video": {
    "width":1280,
    "height": 720,
    "bitrate": "5000k",
    "framerate": 30
  },
  "audio": {
    "bitrate": "160k",
    "samplingrate": 44100
  }
}
```

You can add your on definitions and add them to the $config variable:

```php
$config = array(
  'profile.dirs' => array('YOUR PROFILE PATH')
);
```


## API

```php
Html5Video($config = array(), $process = null, $cache = null)
```
Constructor

`$config` has following optional options:

  * `ffmpeg.bin`: Binary of ffmpeg. Default is `ffmpeg`
  * `qt-faststart.bin`: Binary of qt-faststart. Default is `qt-faststart`
  * `profile.dirs`: List of profile directories
  * `videoContainers`: Additional video container formats. Array with `videoEncoder` and `audioEncoder` settings. Eg `'videoContainers' => array('flv' => array('videoEncoder' => 'flv', 'audioEncoder' => 'mp3'))`
  * `timelimit`: Time limit in seconds. 0 for no time. Default is 0

`$process`: (Optional) Process object to call external ffmpeg process. See `Html5Video/Process/IProcess.php`
`$cache`: (Optional) Cache object to store ffmpeg settings. See `Html5Video/Cache/ICache.php`

```php
Html5Video::convert($src, $dst, $profileName, $options = array())
```
Convert given video file to HTML5 compatible format

Options are:

  * `width`: (int) Width of source video
  * `height`: (int) Height of source video
  * `audio`: (bool) Enable/disable audio track
  * `targetFormat`: (string) Target format

If `width` and `height` are given (recommended), Html5Video does not read source
video file to calculate resize sizes.


```php
(array) Html5Video::getVideoInfo($src)
```
Get basic information about video file. Values contain

  * `width`: Width of video
  * `height`: Height of video
  * `duration`: Duration of video in seconds
  * `videoStreams`: Number of video streams
  * `audioStreams`: Number of audio streams

```php
(array) Html5Video::listProfiles()
```
Get list of available profile names


```php
(object) Html5Video::getProfile($name)
```
Get profile data

```php
(array) Html5Video::getVersion()
```
Get current ffmpeg version as array


```php
(array) Html5Video::getEncoders()
```
Get list of supported encoders


Testing
-------

For testing [phpunit](https://github.com/sebastianbergmann/phpunit) is used. Run

```bash
$ phpunit
```

in `html5-video-php`.


License
-------

MIT License. See LICENSE file
