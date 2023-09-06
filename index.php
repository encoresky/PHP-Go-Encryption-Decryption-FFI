<?php
function encryptString($plainText, $key) {
    $ivSize = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivSize, $isStrong);
    if (!$isStrong) {
        throw new Exception('IV generation failed');
    }
    $cipherText = openssl_encrypt($plainText, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $cipherText);
}

function stringToGoString($goStr, $string) {
    $strChar = str_split($string);

    $c = FFI::new('char[' . count($strChar) . ']', false);
    foreach ($strChar as $i => $char) {
        $c[$i] = $char;
    }
    
    $goStr->p = FFI::cast(FFI::type('char *'), $c);
    $goStr->n = count($strChar);

    return $goStr;
}

// Example usage:
$plaintextString = "I am here to solve the problem.";
$encryptionKey = "1234567890abcdef1234567890abcdef";
$encryptedString = encryptString($plaintextString, $encryptionKey);

echo "Encrypted String: " . $encryptedString. "\n";

$ffi = FFI::cdef("
typedef struct { const char *p; long n; } GoString;
GoString DecryptString(GoString encryptedBase64, GoString key);
", __DIR__ . "/decrypt.so");

$encryptedString = stringToGoString($ffi->new("GoString"), $encryptedString);
$encryptionKey = stringToGoString($ffi->new("GoString"), $encryptionKey);
print_r ($ffi->DecryptString($encryptedString , $encryptionKey))
?>
