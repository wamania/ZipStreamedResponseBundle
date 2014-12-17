<?php

namespace Wamania\ZipStreamedResponseBundle\Response\ZipStreamer;

use Symfony\Component\HttpFoundation\File\File;

abstract class ZipStreamerBaseFile extends File
{
    /**
     * Name of the file in Zip
     *
     * @var String
     */
    protected $inZipFilename;

    /**
     * Cyclic Redundancy Check - on 32bits - of the file
     *
     * @var Bytes
     */
    protected $crc32;

    /**
     * Length of compressed data
     *
     * @var int
     */
    protected $compressedLength;

    /**
     * Length of uncompressed data
     *
     * @var int
     */
    protected $uncompressedLength;

    /**
     * Last modified datetime in DOS format
     *
     * @var Bytes
     */
    protected $dosTime;

    /**
     * start offset in final zip file
     *
     * @var int
     */
    protected $offset;

    /**
     * Length of centralDirectoryRecord
     *
     * @var int
     */
    protected $cdrLength;

    /**
     * Constructs a new file from the given path.
     *
     * @param string $path          The path to the file
     * @param bool $checkPath       Whether to check the path or not
     *
     * @api
     */
    public function __construct($path, $checkPath = true)
    {
        $this->compressedLength = 0;
        $this->uncompressedLength = 0;

        parent::__construct($path, $checkPath);
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
        $localFileHeader =
            ZipStreamerConstants::ZIP_LOCAL_FILE_HEADER
            . ZipStreamerConstants::ATTR_VERSION_TO_EXTRACT // version needed to extract
            . $generalPurpose                    // gen purpose bit flag
            . $compressionMethod                   // compression method
            . pack('V', $this->dosTime)
            . pack('V', $this->crc32)               // crc32
            . pack('V', $this->compressedLength)  // compressed filesize
            . pack('V', $this->uncompressedLength)// uncompressed filesize
            . pack('v', strlen($this->inZipFilename))  // length of filename
            . pack('v', 0)                  // extra field length
            . $this->inZipFilename;

        echo $localFileHeader;

        return strlen($localFileHeader);
    }

    /**
     * Send data descriptor
     *
     * @return void
     */
    public function sendDataDescriptor()
    {
        $dataDescriptor =
        ZipStreamerConstants::ZIP_DATA_DESCRIPTOR_HEADER
        . pack('V', $this->crc32)                  // crc32
        . pack('V', $this->compressedLength)     // compressed filesize
        . pack('V', $this->uncompressedLength);   // uncompressed filesize

        echo $dataDescriptor;

        return strlen($dataDescriptor);
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
        $centralDirectoryRecord =
            ZipStreamerConstants::ZIP_CENTRAL_FILE_HEADER
            . ZipStreamerConstants::ATTR_MADE_BY_VERSION     // version made by
            . ZipStreamerConstants::ATTR_VERSION_TO_EXTRACT  // version needed to extract
            . $generalPurpose                               // gen purpose bit flag
            . $compressionMethod                            // compression method
            . pack('V', $this->dosTime)                     // last mod time & date
            . pack('V', $this->crc32)                       // crc32
            . pack('V', $this->compressedLength)            // compressed filesize
            . pack('V', $this->uncompressedLength)          // uncompressed filesize
            . pack('v', strlen($this->inZipFilename)) // length of filename
            . pack('v', 0 )                                 // extra field length
            . pack('v', 0 )                                 // file comment length
            . pack('v', 0 )                                 // disk number start
            . pack('v', 0 )                                 // internal file attributes
            . pack('V', 32 )                                // external file attributes - 'archive' bit set
            . pack('V', $this->offset )                     // relative offset of local header
            . $this->inZipFilename;

        $this->cdrLength = strlen($centralDirectoryRecord);

        echo $centralDirectoryRecord;
    }

    /**
     * Return the central directory record length
     *
     * @return int length
     */
    public function getCdrLength()
    {
        return $this->cdrLength;
    }

    /**
     * Set the offset for this file in the zip file
     *
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Last modified datetime in DOS format
     *
     * @return 2 Bytes
     */
    public function getDosDatetime()
    {
        $date = \DateTime::createFromFormat('U', $this->getMTime());

        if ($date->format('Y') < 1980) {
            $date->setDate(1980, 1, 1);
        }

        return (
            (intval($date->format('Y')) - 1980) << 25)    // year
            | (intval($date->format('n')) << 21)            // month
            | (intval($date->format('j')) << 16)            // day
            | (intval($date->format('G')) << 11)            // hour
            | (intval($date->format('i')) << 5)             // minute
            | (intval($date->format('s')) >> 1);            // second
    }
}