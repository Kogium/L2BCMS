<?php
/**
 * @copyright (c) 2014-2018 Kogium.
 * @author Kogium <kogium@valkyrie-group.info>
 *
 * @licence CC BY-SA 4.0
 * @licence http://creativecommons.org/licenses/by-sa/4.0/deed.fr
 *
 */

/**
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/* @ignore IF CLASS EXIST SECURITY */
if ( !class_exists('cipher_system') ) {

    class cipher_system
    {
        // -----------------------------------------
        // crypte une chaine (via une clé de cryptage)
        // -----------------------------------------
        function encrypt_128_caesar_cipher($maCleDeCryptage=NULL, $maChaineACrypter=NULL)
        {
            GLOBAL $lang;

            if (is_null($maCleDeCryptage) || is_null($maChaineACrypter))
                throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

            $maCleDeCryptage = hash('sha512', $maCleDeCryptage);
            $letter = -1;
            $newstr = '';
            $strlen = strlen($maChaineACrypter);
            for ($i = 0; $i < $strlen; $i++) {
                $letter++;
                if ($letter > 31) {
                    $letter = 0;
                }
                $neword = ord($maChaineACrypter{$i}) + ord($maCleDeCryptage{$letter});
                if ($neword > 255) {
                    $neword -= 256;
                }
                $newstr .= chr($neword);
            }
            return base64_encode($newstr);
        }

        // -----------------------------------------
        // décrypte une chaine (avec la même clé de cryptage)
        // -----------------------------------------
        function uncrypt_128_caesar_cipher($maCleDeCryptage=NULL, $maChaineCrypter=NULL)
        {
            GLOBAL $lang;

            if (is_null($maCleDeCryptage) || is_null($maChaineCrypter))
                throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

            $maCleDeCryptage = hash('sha512', $maCleDeCryptage);
            $letter = -1;
            $newstr = '';
            $maChaineCrypter = base64_decode($maChaineCrypter);
            $strlen = strlen($maChaineCrypter);
            for ($i = 0; $i < $strlen; $i++) {
                $letter++;
                if ($letter > 31) {
                    $letter = 0;
                }
                $neword = ord($maChaineCrypter{$i}) - ord($maCleDeCryptage{$letter});
                if ($neword < 1) {
                    $neword += 256;
                }
                $newstr .= chr($neword);
            }
            return $newstr;
        }
    }
}
?>
