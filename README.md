Create HTML5 Videos
===================

html5-video-php is a ffmpeg wrapper to create HTML5 video formats like
MP4, WEBM, and OGG to support all common mobile and desktop devices.


Requirements
------------

* [ffmpeg](http://www.ffmpeg.org) with x264 support
* qt-faststart (comes with ffmpeg)

Following ffmpeg versions from 0.6 are supported


Installing
----------

html5-video-php uses [composer](http://getcomposer.org) 

  $ curl -sS https://getcomposer.org/installer | php


Usage
-----

Include html5-video-php in your composer.json file

    {
      "require": {
        "xemle/html5-video-php": "1.0.*"
      }
    }

Create a H264 video

    $config = array(
      'ffmpeg.bin' => '/usr/bin/ffmpeg',
      'qt-faststart.bin' => '/usr/bin/qt-faststart',
    );
    $profileName = '720p-hd'; // other profiles are listed in src/Html5Video/profiles
    $targetFormat = 'mp4';    // targetFormat is on of mp4, webm, or ogg

    $html5 = new Html5Video\Html5Video($config);
    $html5->create($srcVideo, $targetVideo, $targetFormat, $profileName);


Profiles
--------

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

You can add your on definitions and add them to the $config variable:

    $config = array(
      'profile.dirs' => array('YOUR PROFILE PATH')
    );


API
---

Convert given video file to HTML5 compatible format

    create($src, $dst, $targetFormat, $profileName, $options = array())

Get basic information about video file

    getVideoInfo($src)

Get list of available profile names

    listProfiles()

Get profile data

    getProfile($name)

Get current ffmpeg version as array

    getVersion() {

Get list of supported encoders

    getEncoders() {


Testing
-------

For testing [phpunit](https://github.com/sebastianbergmann/phpunit) is used. Run

    $ phpunit

in html5-video-php.


License
-------

MIT License. See LICENSE file