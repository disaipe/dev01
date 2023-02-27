export function bufferToBase64(buffer) {
    return new Promise((resolve) => {
       const reader = new FileReader();
       reader.onload = () => resolve(reader.result);
       reader.readAsDataURL(new Blob([buffer]));
    }).then((base64url) => {
        return base64url.split(',', 2)[1];
    });
}

export function base64ToBuffer(base64) {
    const dataUrl = 'data:application/octet-binary;base64,' + base64;

    return fetch(dataUrl)
        .then((response) => response.arrayBuffer())
        .then((buffer) => {
            return new Uint8Array(buffer);
        });
}
