export function bufferToBase64(buffer: BlobPart) {
  return new Promise<string | undefined>((resolve) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result?.toString());
    reader.readAsDataURL(new Blob([buffer]));
  }).then((base64url) => {
    return base64url?.split(',', 2)[1];
  });
}

export function base64ToBuffer(base64: string): Promise<Uint8Array> {
  const dataUrl = `data:application/octet-binary;base64,${base64}`;

  return fetch(dataUrl)
    .then(response => response.arrayBuffer())
    .then(buffer => new Uint8Array(buffer));
}
