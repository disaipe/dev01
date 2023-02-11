import CryptoJS from 'crypto-js';

/**
 * Decrypt data with given key
 *
 * @param {string} data
 * @param {string} key
 * @return {Promise<string>}
 */
export function decrypt(data, key) {
    const encryptStr = CryptoJS.enc.Base64.parse(data);
    const encryptData = JSON.parse(encryptStr.toString(CryptoJS.enc.Utf8));

    const iv = CryptoJS.enc.Base64.parse(encryptData.iv);

    const decrypted = CryptoJS.AES.decrypt(encryptData.value, CryptoJS.enc.Base64.parse(key), {
        iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    });

    const decryptedString = CryptoJS.enc.Utf8.stringify(decrypted);

    return Promise.resolve(decryptedString);
}
