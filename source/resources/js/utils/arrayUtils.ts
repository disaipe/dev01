export function resizeArray(array: any[], newSize: number, fill: any = undefined): any[] {
  const changeSize = newSize - array.length;
  if (changeSize > 0) {
    return array.concat(Array.from({ length: changeSize }).fill(fill));
  }
  return array.slice(0, newSize);
}

export function makeMatrix(rows: number, cols: number, defaults: any = undefined): any[] {
  return Array.from(Array.from({ length: rows }), () => Array.from({ length: cols }).fill(undefined));
}
