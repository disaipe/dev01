interface CookieOptions {
  'path'?: string;
  'expires'?: Date | string;
  'max-age'?: number;

  [name: string]: string | number | boolean | Date | undefined;
}

export function setCookie(name: string, value: string | number | boolean, options: CookieOptions = {}) {
  options = {
    path: '/',
    ...options,
  };

  if (options.expires instanceof Date) {
    options.expires = options.expires.toUTCString();
  }

  let updatedCookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}`;

  for (const optionKey of Object.keys(options)) {
    updatedCookie += `; ${optionKey}`;

    const optionValue = options[optionKey];

    if (optionValue !== true) {
      updatedCookie += `=${optionValue}`;
    }
  }

  document.cookie = updatedCookie;
}

export function getCookie(name: string): string | undefined {
  const matches = document.cookie.match(new RegExp(
    `(?:^|; )${name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1')}=([^;]*)`,
  ));

  return matches ? decodeURIComponent(matches[1]) : undefined;
}

export function deleteCookie(name: string) {
  setCookie(name, '', {
    'max-age': -1,
  });
}

export default {
  setCookie,
  getCookie,
  deleteCookie,
};
