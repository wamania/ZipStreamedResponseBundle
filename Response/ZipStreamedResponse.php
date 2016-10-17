<?php

namespace Wamania\ZipStreamedResponseBundle\Response;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * ZipStreamedResponse represents a streamed HTTP response of a Zip file build on the fly.
 *
 * A ZipStreamedResponse uses a ZipStreamer object
 *
 * $zipStreamer = new ZipStreamer();
 * $zipStreamer->add(new ZipStreamerFile('/home/wamania/movie.mp4', 'movies/movie.mp4'));
 * $zipStreamer->add('/home/wamania/photo.jpg', 'images/photo.jpg');
 * $zipStreamer->add(new ZipStreamerBigFile('/home/wamania/the-big-movie.mp4', 'movies/the-big-movie.mp4'));
 * $zipStreamer->addBigFile('/home/wamania/another-big-movie.mp4', 'movies/another-big-movie.mp4');
 *
 * return new ZipStreamedResponse($zipStreamer);
 *
 * @author Guillaume Affringue <wamania@yahoo.fr>
 *
 * @api
 */
class ZipStreamedResponse extends StreamedResponse
{
    /**
     * ZipStreamer represents a container for the files to add in the Zip file
     *
     * @var ZipStreamer\ZipStreamer
     */
    private $zipStreamer;

    /**
     * Constructor
     *
     * @param ZipStreamer\ZipStreamer $zipStreamer
     * @param number $status
     * @param Array $headers
     */
    public function __construct(ZipStreamer\ZipStreamer $zipStreamer, $status = 200, $headers = array())
    {
        $this->zipStreamer = $zipStreamer;

        $callback = function() use ($zipStreamer)
        {
            $zipStreamer->send();
            $zipStreamer->finalize();
        };

        parent::__construct($callback, $status, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Request $request)
    {
        $this->headers->set('Cache-Control', 'no-cache');
        $this->headers->set('Content-Type', 'application/zip');

        $this->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->zipStreamer->getName(),
            $this->zipStreamer->getNameFallback()
            );

        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        $this->ensureIEOverSSLCompatibility($request);

        return parent::prepare($request);
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition      ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename         Optionally use this filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return BinaryFileResponse
     */
    public function setContentDisposition($disposition, $filename = '', $filenameFallback = '')
    {
        if ($filename === '') {
            $filename = $this->file->getFilename();
        }

        $dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers->set('Content-Disposition', $dispositionHeader);

        return $this;
    }
}
