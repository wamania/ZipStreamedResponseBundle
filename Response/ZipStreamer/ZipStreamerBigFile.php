<?php

namespace Wamania\ZipStreamedResponseBundle\Response\ZipStreamer;

class ZipStreamerBigFile extends ZipStreamerBaseFile
{
    private static $bufferSize = 1048576; // 1Mb

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
     * Calculate some values once
     *
     * @return void
     */
    public function process()
    {
        $this->dosTime = $this->getDosDatetime();
    }

    /**
     * Build and send local file header
     *
     * @return void
     */
    public function sendLocalFileHeader(
        $generalPurpose     = ZipStreamerConstants::ZIP_GENERAL_PURPOSE_DATA_DESCRIPTOR,
        $compressionMethod  = ZipStreamerConstants::ZIP_COMPRESSION_METHOD_STORE)
    {
        return parent::sendLocalFileHeader($generalPurpose, $compressionMethod);
    }

    /**
     * Send "compressed" data (no compression here, method is STORE)
     *
     * @return void
     */
    public function sendZdata()
    {
        $hash_crc = hash_init('crc32b');

        $fh = fopen($this->getPathname(), 'rb');

        while ($data = fread($fh, self::$bufferSize)) {
            hash_update($hash_crc, $data);
            $this->uncompressedLength += strlen($data);

            echo $data;
        }

        fclose($fh);

        $this->crc32 = hexdec(hash_final($hash_crc));
        $this->compressedLength = $this->uncompressedLength;

        return $this->compressedLength;
    }

    /**
     * Send central directory record
     *
     * @return void
     */
    public function sendCentralDirectoryRecord(
        $generalPurpose     = ZipStreamerConstants::ZIP_GENERAL_PURPOSE_DATA_DESCRIPTOR,
        $compressionMethod  = ZipStreamerConstants::ZIP_COMPRESSION_METHOD_STORE)
    {
        parent::sendCentralDirectoryRecord($generalPurpose, $compressionMethod);
    }

    /**
     * Set buffer size in bytes
     *
     * @param int $bufferSize
     */
    public static function setBufferSize($bufferSize)
    {
        self::$bufferSize = $bufferSize;
    }
}