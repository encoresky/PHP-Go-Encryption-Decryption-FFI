package main

import (
	"C"
	"fmt"
)
import (
	"crypto/aes"
	"crypto/cipher"
	"encoding/base64"
)

//export DecryptString
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

// func main() {
// remove this function after making build
// }
