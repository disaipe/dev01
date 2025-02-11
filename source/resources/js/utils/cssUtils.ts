interface Size {
  value: number | null;
  unit: string | null;
}

export function parseSize(value: string): Size {
  if (value) {
    // eslint-disable-next-line regexp/no-misleading-capturing-group
    const groups = value.match(/(?<value>[\d.]+)(?<unit>\w+)/)?.groups;

    if (groups) {
      return {
        value: Number.parseFloat(groups.value),
        unit: groups.unit,
      };
    }
  }

  return {
    value: null,
    unit: null,
  };
}

export function pxToPt(value: number): number {
  return Math.round(3 / 4 * value);
}

export function ptToPx(value: number): number {
  return Math.round(value / (3 / 4));
}

export function ptToEm(value: number): number {
  return Math.round(value / 12);
}
