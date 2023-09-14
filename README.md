# Cross-Language Data Encryption: PHP and Golang with PHP FFI
In today's interconnected world, data security is of paramount importance. Whether you are developing a web application or a backend service, encrypting sensitive information before transmitting or storing it is a common practice and a general requirement.

In this tutorial, I’ll explore a unique approach to encrypting and decrypting data using two different technologies: PHP and Golang. By utilizing the PHP FFI (Foreign Function Interface) function, this combination of languages allows you to achieve robust encryption and decryption processes while leveraging the strengths of both PHP and Golang.

## Prerequisites: ##
Before we begin, make sure you have the following tools installed on your system.
- PHP(enabled FFI)
- Golang (latest version)

## Step 1: Setting up PHP Encryption ##
Let's begin by implementing encryption in PHP using OpenSSL. Here's the PHP code to encrypt a string:
```PHP
    function encryptString($plainText, $key) {
        $ivSize = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivSize, $isStrong);
        if (!$isStrong) {
            throw new Exception('IV generation failed');
        }
        $cipherText = openssl_encrypt($plainText, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $cipherText);
    }
```
This function takes two arguments: the message to encrypt and a key. It uses AES-256-CBC encryption with a randomly generated initialization vector (IV) for data encryption.

You can use this function as follows:
```PHP
    // Example usage:
    $plaintextString = "I am here to solve the problem.";
    $encryptionKey = "1234567890abcdef1234567890abcdef";
    $encryptedString = encryptString($plaintextString, $encryptionKey);

    echo "Encrypted String: " . $encryptedString. "\n";
```
## Step 2: Setting up Golang Decryption ##
Now, let's create a Golang program to decrypt the data encrypted by PHP. Here's the Golang code:
```GOLANG
    // DecryptString decrypts a Base64-encoded encrypted string using the specified key in AES-256-CBC mode.
    // It takes two arguments: the encryptedBase64 string containing the encrypted data and the decryption key.
    // If decryption is successful, it returns a pointer to a C-style string (char*).
    // If any errors occur during decryption or if the ciphertext is too short, it returns nil.
    // This function performs the decryption and also removes any padding applied during encryption.
    func DecryptString(encryptedBase64, key string) *C.char {
        encrypted, err := base64.StdEncoding.DecodeString(encryptedBase64)
        if err != nil {
            return nil
        }

        block, err := aes.NewCipher([]byte(key))
        if err != nil {
            return nil
        }

        if len(encrypted) < aes.BlockSize {
            fmt.Println("ciphertext too short")
            return nil
        }

        iv := encrypted[:aes.BlockSize]
        encrypted = encrypted[aes.BlockSize:]

        mode := cipher.NewCBCDecrypter(block, iv)
        mode.CryptBlocks(encrypted, encrypted)

        // Remove padding
        padding := int(encrypted[len(encrypted)-1])
        return C.CString(string(encrypted[:len(encrypted)-padding]))
    }
```
This Golang code defines a function for decryption using the OpenSSL library.

## Step 3: Calling Golang Decrypt Method using PHP FFI ##
PHP FFI is a feature introduced in PHP 7.4 that allows you to call functions from shared libraries (DLLs or SO files) written in other programming languages, such as C or C++, directly from your PHP code. This enables you to integrate low-level system libraries or interact with native code in your PHP applications.


In this code, we define a C function prototype and specify the shared library's location.
```PHP
    $ffi = FFI::cdef("
        typedef struct { const char *p; long n; } GoString;
        GoString DecryptString(GoString encryptedBase64, GoString key);
        ", __DIR__ . "/decrypt.so");
```
- We use FFI::cdef() method to define the prototype of a C function that we want to call from the shared library. In this case, it's GoString DecryptString(GoString encryptedBase64, GoString key);.
- We specify the name of the shared library: for example, "DIR . "/decrypt.so" on Linux. Ensure the library is located in a directory that PHP can access.
- We define GoString data type to interchange information in different languages.


We also create a helper function `stringToGoString` to convert a PHP string into a format suitable for Go:
```PHP
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
```
- The main purpose of `stringToGoString` function is to convert a PHP string into a format that can be used as a Go string
- The expression `FFI::new('char[' . count($strChar) . ']', false)` creates a new FFI char array. Its size is determined by the number of characters in the PHP string, and this array will store each individual character of the string.


Finally, we call the Golang Decrypt function using PHP FFI:
```PHP
    $encryptedString = stringToGoString($ffi->new("GoString"), $encryptedString);
    $encryptionKey = stringToGoString($ffi->new("GoString"), $encryptionKey);
    print_r ($ffi->DecryptString($encryptedString , $encryptionKey))
```
## Step 4: Running the Programs ##
- Save the PHP code in a file (e.g., index.php or encrypt.php) and run it to obtain the base64-encoded encrypted data
- Replace `encryptionKey`, `encryptedString` with the respective values.
- Save the Golang code in a file (e.g., main.go or decrypt.go)
- Run the Golang program using the following command
```bash
    go build -o decrypt.so -buildmode=c-shared
    # Note that this should be run each time the Go file is modified
```
- You should see the decrypted data printed to the console.

## Conclusion ##
In this exploration, we took a unique approach to data encryption and decryption, bridging the gap between two powerful programming languages: PHP and Golang. Using the PHP FFI (Foreign Function Interface) function allowed us to leverage the strengths of both languages, resulting in a robust and secure encryption and decryption process.




