/**
 * Encrypts data with a generated symmetric key, then encrypts key with
 * the provided public key.
 * @param data              Data to be encrypted.
 * @param publicKey         Public key to encrypt the symmetric key with.
 * @returns {{data, key}}   {Encrypted data, encrypted key}
 */
function fullEncrypt(data, publicKey) {

    // Generate symmetric key
    var symkey = generateSymmetricKey();

    // Encrypt data with symmetric key
    var encryptedData = encryptAES(data, symkey);

    // Encrypt symmetric key with public ECC key
    var encryptedKey = encryptECC(symkey, publicKey);

    return {"data": encryptedData, "key": encryptedKey}
}

/**
 * Decrypts data with the provided encrypted key and private key.
 * @param encryptedData     Data to be decrypted.
 * @param encryptedKey      Symmetric key to be decrypted.
 * @param privateKey        Private key to decrypt the symmetric key with.
 * @returns {string}        Decrypted data.
 */
function fullDecrypt(encryptedData, encryptedKey, privateKey) {

    try {

        // Decrypt symmetric key
        var decryptedKey = decryptECC(encryptedKey, privateKey);

        // Decrypt encrypted data
        var decryptedData = decryptAES(encryptedData, decryptedKey);

        return decryptedData.toString(CryptoJS.enc.Utf8);

    } catch (e) {
        throw e;
    }

}

/**
 * Encrypts data with a public EC key.
 * Uses SJCL.
 * @param data
 * @param publickey
 */
function encryptECC(data, publickey) {
    return ecc.encrypt(publickey, data);
}

/**
 * Decrypts data with a private EC key.
 * Uses SJCL.
 * @param encrypted
 * @param privatekey
 */
function decryptECC(encrypted, privatekey) {
    return ecc.decrypt(privatekey, encrypted);
}

/**
 * Encrypts data with an AES-256 key.
 * Uses CryptoJS.
 * @param data
 * @param key
 */
function encryptAES(data, key) {
    return CryptoJS.AES.encrypt(data, key);
}

/**
 * Decrypts data with an AES-256 key.
 * Uses CryptoJS.
 * @param encrypted
 * @param key
 */
function decryptAES(encrypted, key) {
    return CryptoJS.AES.decrypt(encrypted, key);
}

/**
 * Generates an unsafe symmetric key to use with AES.
 * @returns {string}
 */
function generateSymmetricKey() {
    
    var salt = CryptoJS.lib.WordArray.random(128/8);
    var password = CryptoJS.lib.WordArray.random(128/8);

    return CryptoJS.PBKDF2(password, salt, { keySize: 512/32, iterations: 1 }).toString();

}

/**
 * Generates an EC public-private key pair.
 * @returns {{public, private: (dec|{})}}
 */
function generateKeypair() {
    var keypair = ecc.generate(ecc.ENC_DEC);
    return {"public" : keypair.enc, "private" : keypair.dec};
}

/**
 * Generates a PBKDF2 hash from the specified password and salt
 */
function generatePBKDF2(password, salt) {
    return CryptoJS.PBKDF2(password, salt, {keySize: 512/32, iterations: 16000})
}