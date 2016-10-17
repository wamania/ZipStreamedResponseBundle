<?php

namespace Wamania\ZipStreamedResponseBundle\Response\ZipStreamer;

/**
 * ZipStreamer represents a container for the files to add in the Zip file
 * It call and send the Zip construction of each file
 *
 * A ZipStreamer object is used by ZipStreamedResponse
 *
 * @author Guillaume Affringue <wamania@yahoo.fr>
 *
 * @api
 */
class ZipStreamer
{
    /**
     *The filename of the Zip file
     *
     * @var String
     */
    private $name;

    /**
     *The filename ASCII encoded of the Zip file
     *
     * @var String
     */
    private $nameFallback;


    /**
     * Files to add in zip file
     *
     * @var Array
     */
    private $files;

    /**
     * Current offset
     *
     * @var int
     */
    private $offset;

    /**
     * If $autoSwitch==true, files above $switchFromSize will switch to "big files"
     *
     * @var int Size in byte
     */
    private static $switchAboveSize = 16777216; // 16Mb

    /**
     * Auto switch files above $switchAboveSize to "big files"
     *
     * @var Boolean
     */
    private static $autoSwitch = true;

    /**
     * Constructor
     *
     * @param String $name The filename of the Zip file
     */
    public function __construct($name, $nameFallback = '')
    {
        $this->files = array();
        $this->name = $name;
        $this->nameFallback = $nameFallback;
        $this->offset = 0;
    }

    /**
     * Add a file in zip
     * Use DEFLATE compression and load the whole file in RAM
     * You can add here "normal" file and big file instance
     *
     * @param String|ZipStreamerFile|ZipStreamerBigFile $file
     * @param String $inZipFilename
     *
     * @return void
     */
    public function add($file, $inZipFilename)
    {
        if (is_string($file)) {
            $file = new ZipStreamerFile($file, $inZipFilename);
        }

        if (self::$autoSwitch) {
            if ($file->getSize() > self::$switchAboveSize) {
                $file = new ZipStreamerBigFile($file, $inZipFilename);
            }
        }

        $file->process();

        $this->files[] = $file;
    }

    /**
     * Add big file in zip
     * This function use a few RAM (use stream instead of load whole file)
     * and no compression
     *
     * @param String|ZipStreamerBigFile $file
     * @param String $inZipFilename
     *
     * @return void
     */
    public function addBigFile($file, $inZipFilename)
    {
        if (!$file instanceof ZipStreamerBigFile) {
            $file = new ZipStreamerBigFile($file, $inZipFilename);
        }

        $file->process();

        $this->files[] = $file;
    }

    /**
     * THE function !
     * Foreach files, it send headers, data and data descriptor (required for big file)
     * Increase the offset for the next file
     *
     * @return void
     */
    public function send()
    {
        foreach ($this->files as $file) {

            $file->setOffset($this->offset);

            $this->offset += $file->sendLocalFileHeader();
            $this->offset += $file->sendZdata();
            $this->offset += $file->sendDataDescriptor();
        }
    }

    /**
     * Send the Central Directory Record,
     * which contains all the informations about files
     *
     * @return void
     */
    public function finalize()
    {
        $cdrLength = 0;
        foreach ($this->files as $file) {
            $file->sendCentralDirectoryRecord();
            $cdrLength += $file->getCdrLength();
        }

        echo
            ZipStreamerConstants::ZIP_END_OF_CENTRAL_DIRECTORY
            . pack('v', count($this->files))    // total # of entries "on this disk"
            . pack('v', count($this->files))    // total # of entries overall
            . pack('V', $cdrLength)             // size of central dir
            . pack('V', $this->offset)          // offset to start of central dir
            . "\x00\x00";                       // .zip file comment length
    }

    /**
     * Getter for the name of the zip file
     *
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Getter for the fallback name of the zip file
     *
     * @return String
     */
    public function getNameFallback()
    {
        return $this->nameFallback;
    }

    /**
     * Set autoSwitch
     *
     * @param Boolean $autoSwitch
     */
    public static function setAutoSwitch($autoSwitch)
    {
        self::$autoSwitch = $autoSwitch;
    }

    /**
     * Set switchAboveSize
     *
     * @param int $switchAboveSize
     */
    public static function setSwitchAboveSize($switchAboveSize)
    {
        self::$switchAboveSize = $switchAboveSize;
    }
}