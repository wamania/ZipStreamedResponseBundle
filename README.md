ZipStreamedResponseBundle
==================

Largely inspired by
[ZipStream-PHP](https://github.com/maennchen/ZipStream-PHP), 
[PHPZip](https://github.com/paranoiq/PHPZip)
and of course, the [ZIP specifications](http://www.pkware.com/documents/casestudies/APPNOTE.TXT).

* [Installation](#installation)
* [Usage](#usage)


Installation
------------

Require [`wamania/zip-streamed-response-bundle`](https://packagist.org/packages/wamania/zip-streamed-response-bundle)
into your `composer.json` file:


``` json
{
    "require": {
        "wamania/zip-streamed-response-bundle": "dev-master"
    }
}
```
Register the bundle in `app/AppKernel.php`:

``` php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Wamania\ZipStreamedResponseBundle\ZipStreamedResponseBundle(),
    );
}
```

Usage
-----

In your controller :

``` php
// ....
use Wamania\ZipStreamedResponseBundle\Response\ZipStreamer\ZipStreamer;
use Wamania\ZipStreamedResponseBundle\Response\ZipStreamer\ZipStreamerFile;
use Wamania\ZipStreamedResponseBundle\Response\ZipStreamer\ZipStreamerBigFile;
use Wamania\ZipStreamedResponseBundle\Response\ZipStreamedResponse;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $zipStreamer = new ZipStreamer('test.zip');
        
        // Auto switch files above switchAboveSize to "big files"
        ZipStreamer::setAutoSwitch(false/true); // default = true
        
        // If autoSwitch==true, files above $switchAboveSize will switch to "big files"
        ZipStreamer::setSwitchAboveSize(16777216); // default = 16Mb
        
        // For big file, size of the buffer for fread
        ZipStreamerBigFile::setBufferSize(1048576); //default = 1Mb

        // first string is localpath on hdd, second is pathname in zip
        $zipStreamer->add(
          '/home/wamania/photo.jpg', 
          'images/photo.jpg');
          
        // you can send directly a ZipStreamerFile object
        $zipStreamer->add(
          new ZipStreamerFile(
            '/home/wamania/movie.mp4', 
            'movies/movie.mp4'));
        
        // Big files are send by stream instead of being charged whole in RAM
        // Big files are NOT compressed
        $zipStreamer->addBigFile(
          '/home/wamania/another-big-movie.mp4', 
          'movies/another-big-movie.mp4');
          
        // or, directly send a ZipStreamerBigFile object
        $zipStreamer->add(
          new ZipStreamerBigFile(
            '/home/wamania/the-big-movie.mp4', 
            'movies/the-big-movie.mp4'));

        return new ZipStreamedResponse($zipStreamer);
    }
}
```
