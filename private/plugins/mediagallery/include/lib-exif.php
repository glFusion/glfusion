<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* EXIF/IPTC Reading / parsing
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on previous work by
*  Copyright (C) 2004-2008 by the following authors:
*    Bharat Mediratta <bharat@menalto.com>
*    Georg Rehfeld <rehfeld@georg-rehfeld.de>
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'] . 'plugins/mediagallery/include/exif.php';
require_once $_CONF['path'] . 'plugins/mediagallery/include/JPEG.php';


function MG_haveEXIF( $mid ) {
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG04;

    $count      = 0;
    $exifItems  = 0;

    $result = DB_query("SELECT media_filename,media_mime_ext,media_exif FROM {$_TABLES['mg_media']} WHERE media_id='" . DB_escapeString($mid) . "'");
    list($media_filename, $media_mime_ext, $media_exif) = DB_fetchArray($result);
    if ( $media_exif == 0 )
        return 0;

    if ( $media_filename == '' ) {
        return 0;
    }

    $exif = array();
    if ( $_MG_CONF['discard_original'] == 1 ) {
        $exif = ExifProcessor( $_MG_CONF['path_mediaobjects'] . 'disp/' . $media_filename[0] .'/' . $media_filename . '.jpg' );
    } else {
        $exif = ExifProcessor( $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] .'/' . $media_filename . '.' . $media_mime_ext );
    }

    if ( count($exif) == 0 ) {
        DB_query("UPDATE {$_TABLES['mg_media']} SET media_exif=0 WHERE media_id='" . DB_escapeString($mid) . "'");
        return 0;
    }

    return count($exif);
}

/**
* Build the HTML of EXIF/IPTC metadata for an item
*
* This will return the HTML table of the EXIF/IPTC data in an item.
*
* @param        int     $mid            Media ID of item to process
* @param        int     $columns        Number of columns to format output
* @param        int     $mqueue         Are we reading the moderation queue or live media tables
* @return       string  HTML (table) or null string if no metadata available
*
*/
function MG_readEXIF( $mid, $columns = 2, $mqueue=0) {
    global $_CONF, $_MG_CONF, $_TABLES, $LANG_MG01,$LANG_MG04;

    $count      = 0;
    $exifItems  = 0;
    $rowclass   = 1;

    $retval = '';

    $media_filename = DB_getItem($mqueue ? $_TABLES['mg_mediaqueue'] : $_TABLES['mg_media'],'media_filename',"media_id='" .DB_escapeString($mid)."'");
    if ( $media_filename == '' ) {
        return '';
    }
    $media_mime_ext = DB_getItem($mqueue ? $_TABLES['mg_mediaqueue'] : $_TABLES['mg_media'],'media_mime_ext',"media_id='" . DB_escapeString($mid) . "'");

    $aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($mid) . '"');

    // setup the template...
    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file (array ('exif' => 'exif_detail.thtml'));

    $T->set_block('exif', 'exifColumn', 'eColumn');
    $T->set_block('exif', 'exifRow', 'eRow');

    $exif = array();
    if ( $_MG_CONF['discard_original'] == 1 ) {
        $exif = ExifProcessor( $_MG_CONF['path_mediaobjects'] . 'disp/' . $media_filename[0] .'/' . $media_filename . '.jpg' );
    } else {
        $exif = ExifProcessor( $_MG_CONF['path_mediaobjects'] . 'orig/' . $media_filename[0] .'/' . $media_filename . '.' . $media_mime_ext );
    }
    for ($i=0; $i < count($exif); $i++ ) {
        $T->set_var(array(
            'label' => $exif[$i]['title'],
            'value' => $exif[$i]['value']
        ));
        $T->parse('eColumn', 'exifColumn',true);
        $count++;
        if ( $count == $columns ) {
            $T->set_var('rowclass',($rowclass % 2)+1);
            $rowclass++;
            $count = 0;
            $T->parse('eRow','exifRow',true);
            $T->set_var('eColumn','');
        }
        $exifItems++;
    }
    if ( $count != 0 ) {
        $T->parse('eRow','exifRow',true);
    }
    $T->set_var('lang_exifheader',$LANG_MG04['exif_header']);
    $T->set_var('exif_title',$LANG_MG01['image_properties']);
    $T->parse('output','exif');
    $retval .= $T->finish($T->get_var('output'));

    if ( $exifItems == 0 ) {
        return '';
    } else {
        return $retval;
    }
}


/**
* Read EXIF/IPTC data of a file
*
* Pulls the metadata from an item
*
* @param        string  $file           filename to read metadata from
* @return       array   The EXIF/IPTC items for this file
*
* This code from here to the end of this file was borrowed from
* the Gallery v2 EXIF module written by:
*       Bharat Mediratta <bharat@menalto.com>
*       Georg Rehfeld <rehfeld@georg-rehfeld.de>
*
*/
function ExifProcessor( $file ) {

    $rawExifData = array();

    $iptc = new JPEG( $file );

    $rawExifData = read_exif_data_raw( $file, false );

    $exifKeys   = getExifKeys();    // builds an array of the EXIF data we care about...
    $properties = getProperties( );
    $results    = array();

    $pCount = count($properties);
    if ( $pCount > 0 ) {
        foreach( $properties as $property ) {
            $title = $exifKeys[$property][0];
            for ($i=1; $i < sizeof($exifKeys[$property]); $i++) {
                $value = getExifValue($rawExifData, explode('.', $exifKeys[$property][$i]));
                if (!isset($value)) {
                    $value = getIptcValue($iptc,explode('.',$exifKeys[$property][$i]));
                }
                if (isset($value)) {
                    $value = postProcessValue($property, $value);
                    $results[] = array('title' => $title, 'value' => $value);
                    break;
                }
            }
        }
    }
    return( $results );
}

function postProcessValue(  $property, $value ) {
    global $_CONF, $_USER;

    switch($property) {
    case 'ShutterSpeedValue':
        /* Convert "25/10000 sec" to "1/400 sec" */
        $results = sscanf($value, '%d/%d sec');
        if (count($results) == 2 && $results[0] > 0) {
            if ($results[1] % $results[0] == 0) {
                $value = sprintf('1/%d sec', $results[1] / $results[0]);
            }
        }
        break;

    case 'ApertureValue':
        /* Convert "f 2.8" to "f/2.8" */
        $results = sscanf($value, 'f %s');
        if (count($results) == 1) {
            $value = 'f/' . $results[0];
        }
        break;

    case 'DateTime':
        /* Convert to localized string. */
        if (preg_match('#(\d+):(\d+):(\d+)\s+(\d+):(\d+):(\d+)#', $value, $m)) {
            $time = mktime((int)$m[4], (int)$m[5], (int)$m[6],
                           (int)$m[2], (int)$m[3], (int)$m[1]);
        }
        /* This ISO 8601 pattern seems to be used by newer Adobe products */
        else if (preg_match('#(\d+)-(\d+)-(\d+)T(\d+):(\d+):(\d+)(([-+])(\d+)(:(\d+))?)?#',
                 $value, $m)) {
            $time = mktime((int)$m[4], (int)$m[5], (int)$m[6],
                           (int)$m[2], (int)$m[3], (int)$m[1]);
        }
        if (!empty($time)) {
            $dt = MG_getUserDateTimeFormat( $time );
            $value = $dt[0];
        }
        break;
    }
    return $value;
}

function getExifValue(&$source, $keyPath) {
    $key = array_shift($keyPath);
    if (!isset($source[$key])) {
        return null;
    }

    if (empty($keyPath)) {
        return str_replace("\0", '', $source[$key]);
    }

    return getExifValue($source[$key], $keyPath);
}

function getIptcValue(&$object, $keyPath, $sourceEncoding=null) {
    if ($keyPath[0] != 'IPTC') {
        return null;
    }
    $result = $object->getIPTCField($keyPath[1]);
    if ($result == false) {
        return null;
    }
    if (is_array($result)) {
        $result = implode('; ', $result);
    }
    return $result;
}

function getOriginationTimestamp($file) {
    $rawExifData = array();
    $rawExifData = read_exif_data_raw( $file, false );
    /*
     * The method name indicates, that we want the earliest date available for the img.
     * As of specs and practice with raw camera images and Adobe manipulated ones it seems,
     * that SubIFD.DateTimeOriginal and SubIFD.DateTimeDigitized indicate creation time:
     * both are set to shot time by cameras, scanners set only SubIFD.DateTimeDigitized.
     * Adobe sets IFD0.DateTime to the last modification date/time. So we prefer creation
     * dates.
     */
    foreach (array('SubIFD.DateTimeOriginal', 'SubIFD.DateTimeDigitized', 'IFD0.DateTime')
             as $tag) {
        $value = getExifValue($rawExifData, explode('.', $tag));
        if (isset($value)) {
            if (preg_match('#(\d+):(\d+):(\d+)\s+(\d+):(\d+):(\d+)#', $value, $m)) {
                $time = mktime((int)$m[4], (int)$m[5], (int)$m[6],
                               (int)$m[2], (int)$m[3], (int)$m[1]);
            }
            /* This ISO 8601 pattern seems to be used by newer Adobe products */
            else if (preg_match('#(\d+)-(\d+)-(\d+)T(\d+):(\d+):(\d+)(([-+])(\d+)(:(\d+))?)?#',
                      $value, $m)) {
                $time = mktime((int)$m[4], (int)$m[5], (int)$m[6],
                               (int)$m[2], (int)$m[3], (int)$m[1]);
            }
            if (!empty($time)) {
                if (isset($m[8])) {
                    $offset = ((int)$m[9] * 60 + (isset($m[11]) ? (int)$m[11] : 0)) * 60;
                    if ($m[8] == '+') {
                        $time += $offset;
                    }
                    else {
                        $time -= $offset;
                    }
                }
                return $time;
            }
        }
    }
    return null;
}

function getExifKeys() {
    global $LANG_MG04;
    static $data;

    if (!empty($data)) {
        return $data;
    }

    $data['ApertureValue'] =
        array($LANG_MG04['aperture'],
            'SubIFD.ApertureValue', 'SubIFD.FNumber');
    $data['ShutterSpeedValue'] =
        array($LANG_MG04['shutter_speed'],
            'SubIFD.ShutterSpeedValue', 'SubIFD.ExposureTime');
    $data['ISO'] =
        array($LANG_MG04['iso'],
            'SubIFD.MakerNote.ISOSelection','SubIFD.MakerNote.ISOSetting', 'SubIFD.ISOSpeedRatings', 'SubIFD.MakerNote.Settings 1.ISO');
    $data['FocalLength'] =
        array($LANG_MG04['focal_length'],
            'SubIFD.FocalLength');
    $data['Flash'] =
        array($LANG_MG04['flash'],
            'SubIFD.Flash', 'SubIFD.MakerNote.Settings 1.Flash');
    $data['ACDComment'] =
        array($LANG_MG04['acd_comment'],
            'IFD0.ACDComment');
    $data['AEWarning'] =
        array($LANG_MG04['ae_warning'],
            'SubIFD.MakerNote.AEWarning');
    $data['AFFocusPosition'] =
        array($LANG_MG04['af_focus_position'] ,
            'SubIFD.MakerNote.AFFocusPosition');
    $data['AFPointSelected'] =
        array($LANG_MG04['af_point_selected'],
            'SubIFD.MakerNote.Settings 1.AFPointSelected');
    $data['AFPointUsed'] =
        array($LANG_MG04['af_point_used'], 'SubIFD.MakerNote.Settings 4.AFPointUsed');
    $data['Adapter'] =
        array($LANG_MG04['adapter'], 'SubIFD.MakerNote.Adapter');
    $data['Artist'] =
        array($LANG_MG04['artist'], 'IFD0.Artist');
    $data['BatteryLevel'] =
        array($LANG_MG04['battery_level'], 'IFD1.BatteryLevel');
    $data['BitsPerSample'] =
        array($LANG_MG04['bits_per_sample'], 'IFD1.BitsPerSample');
    $data['BlurWarning'] =
        array($LANG_MG04['blur_warning'], 'SubIFD.MakerNote.BlurWarning');
    $data['BrightnessValue'] =
        array($LANG_MG04['brightness'], 'SubIFD.BrightnessValue');
    $data['CCDSensitivity'] =
        array($LANG_MG04['ccd_sensitivity'], 'SubIFD.MakerNote.CCDSensitivity');
    $data['CameraID'] =
        array($LANG_MG04['camera_id'], 'SubIFD.MakerNote.CameraID');
    $data['CameraSerialNumber'] =
        array($LANG_MG04['camera_serial_number'], 'SubIFD.MakerNote.CameraSerialNumber');
    $data['Color'] =
        array($LANG_MG04['color'], 'SubIFD.MakerNote.Color');
    $data['ColorMode'] =
        array($LANG_MG04['color_mode'], 'SubIFD.MakerNote.ColorMode');
    $data['ColorSpace'] =
        array($LANG_MG04['color_space'], 'SubIFD.ColorSpace');
    $data['ComponentsConfiguration'] =
        array($LANG_MG04['component_configuration'], 'SubIFD.ComponentsConfiguration');
    $data['CompressedBitsPerPixel'] =
        array($LANG_MG04['compressed_bits_per_pixel'], 'SubIFD.CompressedBitsPerPixel');
    $data['Compression'] =
        array($LANG_MG04['compression'], 'IFD1.Compression');
    $data['ContinuousTakingBracket'] =
        array($LANG_MG04['continuous_taking_bracket'],
              'SubIFD.MakerNote.ContinuousTakingBracket');
    $data['Contrast'] =
        array($LANG_MG04['contrast'], 'SubIFD.Contrast',
              'SubIFD.MakerNote.Settings 1.Contrast');
    $data['Converter'] =
        array($LANG_MG04['converter'], 'SubIFD.MakerNote.Converter');
    $data['Copyright'] =
        array($LANG_MG04['copyright'], 'IFD0.Copyright');
    $data['CustomFunctions'] =
        array($LANG_MG04['custom_functions'], 'SubIFD.MakerNote.CustomFunctions');
    $data['CustomerRender'] =
        array($LANG_MG04['customer_render'], 'SubIFD.CustomerRender');
    /* See comment inside getOriginationTimestamp() for changed order of physical props. */
    $data['DateTime'] =
        array($LANG_MG04['datetime'], 'SubIFD.DateTimeOriginal',
              'SubIFD.DateTimeDigitized', 'IFD0.DateTime');
    $data['DigitalZoom'] =
        array($LANG_MG04['digital_zoom'],
              'SubIFD.MakerNote.DigitalZoom', 'SubIFD.MakerNote.DigiZoom');
    $data['DigitalZoomRatio'] =
        array($LANG_MG04['digital_zoom_ratio'], 'SubIFD.DigitalZoomRatio');
    $data['DriveMode'] =
        array($LANG_MG04['drivemode'], 'SubIFD.MakerNote.Settings 1.DriveMode');
    $data['EasyShooting'] =
        array($LANG_MG04['easy_shooting'], 'SubIFD.MakerNote.Settings 1.EasyShooting');
    $data['ExposureBiasValue'] =
        array($LANG_MG04['exposure_bias'], 'SubIFD.ExposureBiasValue');
    $data['ExposureIndex'] =
        array($LANG_MG04['exposure_index'], 'IFD1.ExposureIndex', 'SubIFD.ExposureIndex');
    $data['ExposureMode'] =
        array($LANG_MG04['exposure_mode'],
              'SubIFD.ExposureMode', 'SubIFD.MakerNote.Settings 1.ExposureMode');
    $data['ExposureProgram'] =
        array($LANG_MG04['exposure_program'], 'SubIFD.ExposureProgram');
    $data['FileSource'] =
        array($LANG_MG04['file_source'], 'SubIFD.FileSource');
    $data['FirmwareVersion'] =
        array($LANG_MG04['firmware_version'], 'SubIFD.MakerNote.FirmwareVersion');
    $data['FlashBias'] =
        array($LANG_MG04['flash_bias'], 'SubIFD.MakerNote.Settings 4.FlashBias');
    $data['FlashDetails'] =
        array($LANG_MG04['flash_details'], 'SubIFD.MakerNote.Settings 1.FlashDetails');
    $data['FlashEnergy'] =
        array($LANG_MG04['flash_energy'], 'IFD1.FlashEnergy', 'SubIFD.FlashEnergy');
    $data['FlashMode'] =
        array($LANG_MG04['flash_mode'], 'SubIFD.MakerNote.FlashMode');
    $data['FlashPixVersion'] =
        array($LANG_MG04['flash_pix_version'], 'SubIFD.FlashPixVersion');
    $data['FlashSetting'] =
        array($LANG_MG04['flash_setting'], 'SubIFD.MakerNote.FlashSetting');
    $data['FlashStrength'] =
        array($LANG_MG04['flash_strength'], 'SubIFD.MakerNote.FlashStrength');
    $data['FocalPlaneResolutionUnit'] =
        array($LANG_MG04['focal_plane_resolution_unit'],
              'SubIFD.FocalPlaneResolutionUnit');
    $data['FocalPlaneXResolution'] =
        array($LANG_MG04['focal_plane_x_resolution'], 'SubIFD.FocalPlaneXResolution');
    $data['FocalPlaneYResolution'] =
        array($LANG_MG04['focal_plane_y_resolution'], 'SubIFD.FocalPlaneYResolution');
    $data['FocalUnits'] =
        array($LANG_MG04['focal_units'], 'SubIFD.MakerNote.Settings 1.FocalUnits');
    $data['Focus'] =
        array($LANG_MG04['focus'], 'SubIFD.MakerNote.Focus');
    $data['FocusMode'] =
        array($LANG_MG04['focus_mode'],
              'SubIFD.MakerNote.FocusMode', 'SubIFD.MakerNote.Settings 1.FocusMode');
    $data['FocusWarning'] =
        array($LANG_MG04['focus_warning'], 'SubIFD.MakerNote.FocusWarning');
    $data['GainControl'] =
        array($LANG_MG04['gain_control'], 'SubIFD.GainControl');
    $data['ImageAdjustment'] =
        array($LANG_MG04['image_adjustment'], 'SubIFD.MakerNote.ImageAdjustment');
    $data['ImageDescription'] =
        array($LANG_MG04['image_description'], 'IFD0.ImageDescription');
    $data['ImageHistory'] =
        array($LANG_MG04['image_history'], 'SubIFD.ImageHistory');
    $data['ImageLength'] =
        array($LANG_MG04['image_length'], 'IFD1.ImageLength');
    $data['ImageNumber'] =
        array($LANG_MG04['image_number'],
              'IFD1.ImageNumber', 'SubIFD.MakerNote.ImageNumber');
    $data['ImageSharpening'] =
        array($LANG_MG04['image_sharpening'], 'SubIFD.MakerNote.ImageSharpening');
    $data['ImageSize'] =
        array($LANG_MG04['image_size'], 'SubIFD.MakerNote.Settings 1.ImageSize');
    $data['ImageType'] =
        array($LANG_MG04['image_type'], 'IFD0.ImageType', 'SubIFD.MakerNote.ImageType');
    $data['ImageWidth'] =
        array($LANG_MG04['image_width'], 'IFD1.ImageWidth');
    $data['InterColorProfile'] =
        array($LANG_MG04['inter_color_profile'], 'IFD1.InterColorProfile');
    $data['Interlace'] =
        array($LANG_MG04['interlace'], 'IFD1.Interlace');
    $data['InteroperabilityIFD.InteroperabilityIndex'] =
        array($LANG_MG04['interoperability_index'],
              'InteroperabilityIFD.InteroperabilityIndex');
    $data['InteroperabilityIFD.InteroperabilityVersion'] =
        array($LANG_MG04['interoperability_version'],
              'InteroperabilityIFD.InteroperabilityVersion');
    $data['InteroperabilityIFD.RelatedImageFileFormat'] =
        array($LANG_MG04['related_image_file_format'],
              'InteroperabilityIFD.RelatedImageFileFormat');
    $data['InteroperabilityIFD.RelatedImageLength'] =
        array($LANG_MG04['related_image_length'],
              'InteroperabilityIFD.RelatedImageLength');
    $data['InteroperabilityIFD.RelatedImageWidth'] =
        array($LANG_MG04['related_image_width'],
              'InteroperabilityIFD.RelatedImageWidth');
    $data['JPEGTables'] =
        array($LANG_MG04['jpeg_tables'], 'IFD1.JPEGTables');
    $data['JpegIFByteCount'] =
        array($LANG_MG04['jpeg_if_byte_count'], 'IFD1.JpegIFByteCount');
    $data['JpegIFOffset'] =
        array($LANG_MG04['jpeg_if_offset'], 'IFD1.JpegIFOffset');
    $data['JpegQual'] =
        array($LANG_MG04['jpeg_quality'], 'SubIFD.MakerNote.JpegQual');
    $data['LightSource'] =
        array($LANG_MG04['light_source'], 'SubIFD.LightSource');
    $data['LongFocalLength'] =
        array($LANG_MG04['long_focal_length'],
              'SubIFD.MakerNote.Settings 1.LongFocalLength');
    $data['Macro'] =
        array($LANG_MG04['macro'],
              'SubIFD.MakerNote.Macro', 'SubIFD.MakerNote.Settings 1.Macro');
    $data['Make'] =
        array($LANG_MG04['make'], 'IFD0.Make');
    $data['ManualFocusDistance'] =
        array($LANG_MG04['manual_focus_distance'], 'SubIFD.MakerNote.ManualFocusDistance');
    $data['MaxApertureValue'] =
        array($LANG_MG04['max_aperture_value'], 'SubIFD.MaxApertureValue');
    $data['MeteringMode'] =
        array($LANG_MG04['metering_mode'],
              'SubIFD.MeteringMode', 'SubIFD.MakerNote.Settings 1.MeteringMode');
    $data['Model'] =
        array($LANG_MG04['model'], 'IFD0.Model');
    $data['Noise'] =
        array($LANG_MG04['noise'], 'IFD1.Noise');
    $data['NoiseReduction'] =
        array($LANG_MG04['noise_reduction'], 'SubIFD.MakerNote.NoiseReduction');
    $data['Orientation'] =
        array($LANG_MG04['orientation'], 'IFD0.Orientation');
    $data['OwnerName'] =
        array($LANG_MG04['owner_name'], 'SubIFD.MakerNote.OwnerName');
    $data['PhotometricInterpret'] =
        array($LANG_MG04['photometric_interpretation'],
              'IFD0.PhotometricInterpret', 'IFD1.PhotometricInterpretation');
    $data['PhotoshopSettings'] =
        array($LANG_MG04['photoshop_settings'], 'IFD0.PhotoshopSettings');
    $data['PictInfo'] =
        array($LANG_MG04['picture_info'], 'SubIFD.MakerNote.PictInfo');
    $data['PictureMode'] =
        array($LANG_MG04['picture_mode'], 'SubIFD.MakerNote.PictureMode');
    $data['PlanarConfiguration'] =
        array($LANG_MG04['planar_configuration'],
              'IFD1.PlanarConfiguration', 'IFD0.PlanarConfig');
    $data['Predictor'] =
        array($LANG_MG04['predictor'], 'IFD1.Predictor');
    $data['PrimaryChromaticities'] =
        array($LANG_MG04['primary_chromaticities'], 'IFD0.PrimaryChromaticities');
    $data['Quality'] =
        array($LANG_MG04['quality'],
              'SubIFD.MakerNote.Quality', 'SubIFD.MakerNote.Settings 1.Quality');
    $data['ReferenceBlackWhite'] =
        array($LANG_MG04['reference_bw'], 'IFD0.ReferenceBlackWhite');
    $data['RelatedSoundFile'] =
        array($LANG_MG04['related_sound_file'], 'SubIFD.RelatedSoundFile');
    $data['ResolutionUnit'] =
        array($LANG_MG04['resolution_unit'], 'IFD0.ResolutionUnit');
    $data['RowsPerStrip'] =
        array($LANG_MG04['rows_per_strip'], 'IFD1.RowsPerStrip');
    $data['SamplesPerPixel'] =
        array($LANG_MG04['samples_per_pixel'],
              'IFD1.SamplesPerPixel', 'IFD0.SamplePerPixel');
    $data['Saturation'] =
        array($LANG_MG04['saturation'],
              'SubIFD.Saturation', 'SubIFD.MakerNote.Saturation',
              'SubIFD.MakerNote.Settings 1.Saturation');
    $data['SceneCaptureMode'] =
        array($LANG_MG04['scene_capture_mode'], 'SubIFD.SceneCaptureMode');
    $data['SceneType'] =
        array($LANG_MG04['scene_type'], 'SubIFD.SceneType');
    $data['SecurityClassification'] =
        array($LANG_MG04['security_classification'], 'IFD1.SecurityClassification');
    $data['SelfTimer'] =
        array($LANG_MG04['self_timer'], 'SubIFD.MakerNote.Settings 1.SelfTimer');
    $data['SelfTimerMode'] =
        array($LANG_MG04['self_timer_mode'], 'IFD1.SelfTimerMode');
    $data['SensingMethod'] =
        array($LANG_MG04['sensing_method'], 'SubIFD.SensingMethod');
    $data['SequenceNumber'] =
        array($LANG_MG04['sequence_number'], 'SubIFD.MakerNote.Settings 4.SequenceNumber');
    $data['Sharpness'] =
        array($LANG_MG04['sharpness'],
              'SubIFD.Sharpness', 'SubIFD.MakerNote.Sharpness',
              'SubIFD.MakerNote.Settings 1.Sharpness');
    $data['ShortFocalLength'] =
        array($LANG_MG04['short_focal_length'],
              'SubIFD.MakerNote.Settings 1.ShortFocalLength');
    $data['SlowSync'] =
        array($LANG_MG04['slow_sync'], 'SubIFD.MakerNote.SlowSync');
    $data['Software'] =
        array($LANG_MG04['software'], 'IFD0.Software');
    $data['SoftwareRelease'] =
        array($LANG_MG04['software_release'], 'SubIFD.MakerNote.SoftwareRelease');
    $data['SpatialFrequencyResponse'] =
        array($LANG_MG04['spatial_frequency_response'],
              'IFD1.SpatialFrequencyResponse', 'SubIFD.SpacialFreqResponse');
    $data['SpecialMode'] =
        array($LANG_MG04['special_mode'], 'SubIFD.MakerNote.SpecialMode');
    $data['SpectralSensitivity'] =
        array($LANG_MG04['spectral_sensitivity'], 'SubIFD.SpectralSensitivity');
    $data['StripByteCounts'] =
        array($LANG_MG04['strip_byte_counts'], 'IFD1.StripByteCounts');
    $data['StripOffsets'] =
        array($LANG_MG04['strip_offsets'], 'IFD1.StripOffsets');
    $data['SubIFDs'] =
        array($LANG_MG04['subifds'], 'IFD1.SubIFDs');
    $data['SubfileType'] =
        array($LANG_MG04['subfile_type'], 'IFD1.SubfileType');
    $data['SubjectDistance'] =
        array($LANG_MG04['subject_distance'],
              'SubIFD.SubjectDistance', 'SubIFD.MakerNote.Settings 4.SubjectDistance');
    $data['SubjectLocation'] =
        array($LANG_MG04['subject_location'],
              'IFD1.SubjectLocation', 'SubIFD.SubjectLocation');
    $data['SubsecTime'] =
        array($LANG_MG04['subsec_time'], 'SubIFD.SubsecTime');
    $data['SubsecTimeDigitized'] =
        array($LANG_MG04['subsec_time_digitized'], 'SubIFD.SubsecTimeDigitized');
    $data['SubsecTimeOriginal'] =
        array($LANG_MG04['subsec_time_original'], 'SubIFD.SubsecTimeOriginal');
    $data['TIFF/EPStandardID'] =
        array($LANG_MG04['tiff_ep_standard_id'], 'IFD1.TIFF/EPStandardID');
    $data['TileByteCounts'] =
        array($LANG_MG04['tile_byte_counts'], 'IFD1.TileByteCounts');
    $data['TileLength'] =
        array($LANG_MG04['tile_length'], 'IFD1.TileLength');
    $data['TileOffsets'] =
        array($LANG_MG04['tile_offsets'], 'IFD1.TileOffsets');
    $data['TileWidth'] =
        array($LANG_MG04['tile_width'], 'IFD1.TileWidth');
    $data['TimeZoneOffset'] =
        array($LANG_MG04['time_zone_offset'], 'IFD1.TimeZoneOffset');
    $data['Tone'] =
        array($LANG_MG04['tone'], 'SubIFD.MakerNote.Tone');
    $data['TransferFunction'] =
        array($LANG_MG04['transfer_function'], 'IFD1.TransferFunction');
    $data['UserComment'] =
        array($LANG_MG04['user_comment'], 'SubIFD.UserComment', 'IFD0.UserCommentOld');
    $data['Version'] =
        array($LANG_MG04['version'], 'SubIFD.MakerNote.Version');
    $data['WhiteBalance'] =
        array($LANG_MG04['white_balance'],
              'SubIFD.WhiteBalance', 'SubIFD.MakerNote.WhiteBalance',
              'SubIFD.MakerNote.Settings 4.WhiteBalance');
    $data['WhitePoint'] =
        array($LANG_MG04['white_point'], 'IFD0.WhitePoint');
    $data['YCbCrCoefficients'] =
        array($LANG_MG04['ycbcr_coefficients'], 'IFD0.YCbCrCoefficients');
    $data['YCbCrPositioning'] =
        array($LANG_MG04['ycbcr_positioning'], 'IFD0.YCbCrPositioning');
    $data['YCbCrSubSampling'] =
        array($LANG_MG04['ycbcr_sub_sampling'], 'IFD1.YCbCrSubSampling');
    $data['xResolution'] =
        array($LANG_MG04['x_resolution'], 'IFD0.xResolution');
    $data['yResolution'] =
        array($LANG_MG04['y_resolution'], 'IFD0.yResolution');
    $data['ExifImageHeight'] =
        array($LANG_MG04['exif_image_height'], 'SubIFD.ExifImageHeight');
    $data['ExifImageWidth'] =
        array($LANG_MG04['exif_image_width'], 'SubIFD.ExifImageWidth');
    /* IPTC fields, see http://www.iptc.org/IIM/, if you have time to waste. */
    $data['IPTC/SupplementalCategories'] =
        array($LANG_MG04['iptc_supplemental_categories'], 'IPTC.SupplementalCategories');
    $data['IPTC/Keywords'] =
        array($LANG_MG04['iptc_keywords'], 'IPTC.Keywords');
    $data['IPTC/Caption'] =
        array($LANG_MG04['iptc_caption'], 'IPTC.Caption');
    $data['IPTC/CaptionWriter'] =
        array($LANG_MG04['iptc_caption_writer'], 'IPTC.CaptionWriter');
    $data['IPTC/Headline'] =
        array($LANG_MG04['iptc_headline'], 'IPTC.Headline');
    $data['IPTC/SpecialInstructions'] =
        array($LANG_MG04['iptc_special_instructions'], 'IPTC.SpecialInstructions');
    $data['IPTC/Category'] =
        array($LANG_MG04['iptc_category'], 'IPTC.Category');
    $data['IPTC/Byline'] =
        array($LANG_MG04['iptc_byline'], 'IPTC.Byline');
    $data['IPTC/BylineTitle'] =
        array($LANG_MG04['iptc_byline_title'], 'IPTC.BylineTitle');
    $data['IPTC/Credit'] =
        array($LANG_MG04['iptc_credit'], 'IPTC.Credit');
    $data['IPTC/Source'] =
        array($LANG_MG04['iptc_source'], 'IPTC.Source');
    $data['IPTC/CopyrightNotice'] =
        array($LANG_MG04['iptc_copyright_notice'], 'IPTC.CopyrightNotice');
    $data['IPTC/ObjectName'] =
        array($LANG_MG04['iptc_object_name'], 'IPTC.ObjectName');
    $data['IPTC/City'] =
        array($LANG_MG04['iptc_city'], 'IPTC.City');
    $data['IPTC/ProvinceState'] =
        array($LANG_MG04['iptc_province_state'], 'IPTC.ProvinceState');
    $data['IPTC/CountryName'] =
        array($LANG_MG04['iptc_country_name'], 'IPTC.CountryName');
    $data['IPTC/OriginalTransmissionReference'] =
        array($LANG_MG04['iptc_original_transmission_reference'],
            'IPTC.OriginalTransmissionReference');
    $data['IPTC/DateCreated'] =
        array($LANG_MG04['iptc_date_created'], 'IPTC.DateCreated');
    $data['IPTC/CopyrightFlag'] =
        array($LANG_MG04['iptc_copyright_flag'], 'IPTC.CopyrightFlag');
    $data['IPTC/TimeCreated'] =
        array($LANG_MG04['iptc_time_created'], 'IPTC.TimeCreated');
    return $data;
}


function getProperties ( ) {
    global $_TABLES;

    $result = DB_query("SELECT * FROM {$_TABLES['mg_exif_tags']} WHERE selected=1");
    $nRows = DB_numRows($result);
    for ($i=0; $i < $nRows; $i++ ) {
        $row = DB_fetchArray($result);
        $properties[] = $row['name'];
    }
    return $properties;
}
?>