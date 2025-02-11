import { snakeCase } from 'change-case';

export function snake(str: string): string {
  return snakeCase(str);
}

export function upperFirstLetter(str: string): string {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

export function lowerFirstLetter(str: string): string {
  return str.charAt(0).toLowerCase() + str.slice(1);
}
