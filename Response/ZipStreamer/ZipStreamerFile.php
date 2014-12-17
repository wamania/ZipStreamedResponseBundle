<?php

namespace Wamania\ZipStreamedResponseBundle\Response\ZipStreamer;

class ZipStreamerFile extends ZipStreamerBaseFile
{
    /**
     * Raw data
     *
     * @var unknown
     */
    private $data;

    /**
     * Compressed data
     *
     * @var unknown
     */
    private $zdata;

    /**
     * Constructs a new file from the given path.
     *
     * @param string $path          The path to the file
     * @param string $inZipFilename The futur name in the Zip file
     * @param bool $checkPath       Whether to check the path or not
     *
     * @api
     */
    public function __construct($path, $inZipFilename, $checkPath = true)
    {
        $this->inZipFilename = $inZipFilename;

        parent::__construct($path, $checkPath);
    }

    /**
     * Calculate all values once
     *
     * @return void
     */
    public function process()
    {
        $this->data               = file_get_contents($this->getPathname());
        $this->zdata              = gzcompress($this->data);
        $this->zdata              = substr(substr($this->zdata, 0, strlen($this->zdata) - 4), 2); // fix crc bug
        $this->crc32              = crc32($this->data);
        $this->dosTime            = $this->getDosDatetime();
        $this->compressedLength   = strlen($this->zdata);
        $this->uncompressedLength = strlen($this->data);
    }

    /**
     * Build and send local file header
     *
     * @return void
     */
    public function sendLocalFileHeader(
        $generalPurpose     = ZipStreamerConstants::ZIP_GENERAL_PURPOSE_NONE,
        $compressionMethod  = ZipStreamerConstants::ZIP_COMPRESSION_METHOD_DEFLATE)
    {
        return parent::sendLocalFileHeader($generalPurpose, $compressionMethod);
    }

    /**
     * Send compressed data
     *
     * @return void
     */
    public function sendZdata()
    {
        echo $this->zdata;

        return $this->compressedLength;
    }

    /**
     * Send central directory record
     *
     * @return void
     */
    public function sendCentralDirectoryRecord(
        $generalPurpose     = ZipStreamerConstants::ZIP_GENERAL_PURPOSE_NONE,
        $compressionMethod  = ZipStreamerConstants::ZIP_COMPRESSION_METHOD_DEFLATE)
    {
        parent::sendCentralDirectoryRecord($generalPurpose, $compressionMethod);
    }
}