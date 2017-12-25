<?php

namespace RobThree\Auth\Providers\Qr;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

global $_CONF;

require_once $_CONF['path'].'system/classes/phpqrcode.php';

class glFusionQRProvider implements IQRCodeProvider {
    public function getMimeType() {
        return 'image/png';
    }
    public function getQRCodeImage($qrtext, $size) {
        ob_start();                                     // 'Catch' QRCode's output
        \QRCode::png($qrtext, null, QR_ECLEVEL_L, 3, 4);// We ignore $size and set it to 3
                                                        // since phpqrcode doesn't support
                                                        // a size in pixels...
        $result = ob_get_contents();                    // 'Catch' QRCode's output
        ob_end_clean();                                 // Cleanup
        return $result;                                 // Return image
    }
}