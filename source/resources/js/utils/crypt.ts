import CryptoJS from 'crypto-js';

/**
 * Decrypt data with given key
 *
 * @param {string} data
 * @param {string} key
 * @return {string}
 */
export function decrypt(data: string, key: string): string {
  const encryptStr = CryptoJS.enc.Base64.parse(data);
  const encryptData = JSON.parse(encryptStr.toString(CryptoJS.enc.Utf8));

  const iv = CryptoJS.enc.Base64.parse(encryptData.iv);

  const decrypted = CryptoJS.AES.decrypt(encryptData.value, CryptoJS.enc.Base64.parse(key), {
    iv,
    mode: CryptoJS.mode.CBC,
    padding: CryptoJS.pad.Pkcs7,
  });

  return CryptoJS.enc.Utf8.stringify(decrypted);
}
