<?php

namespace Wamania\ZipStreamedResponseBundle\Response\ZipStreamer;

class ZipStreamerConstants
{
    // Local file header signature
    const ZIP_LOCAL_FILE_HEADER        = "\x50\x4b\x03\x04";

    // Central file header signature
    const ZIP_CENTRAL_FILE_HEADER      = "\x50\x4b\x01\x02";

    // End of Central directory record
    const ZIP_END_OF_CENTRAL_DIRECTORY = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    // Data descriptor signature
    const ZIP_DATA_DESCRIPTOR_HEADER   = "\x50\x4b\x07\x08";

    // Version needed to extract (version 14)
    const ATTR_VERSION_TO_EXTRACT      = "\x0A\x00";

    // Made By Version (Unix, version 14)
    const ATTR_MADE_BY_VERSION         = "\x1E\x03";

    // method STORE (no compression)
    const ZIP_COMPRESSION_METHOD_STORE = "\x00\x00";

    // method DEFLATE, most used in zip
    const ZIP_COMPRESSION_METHOD_DEFLATE = "\x08\x00";

    // nothing to declare
    const ZIP_GENERAL_PURPOSE_NONE = "\x00\x00";

    // tell us that Data Descriptor MUST be send,
    // because crc32&lengths in "local file header" are set to 0
    const ZIP_GENERAL_PURPOSE_DATA_DESCRIPTOR = "\x08\x00";
}