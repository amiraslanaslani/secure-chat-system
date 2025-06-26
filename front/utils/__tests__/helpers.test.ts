import { hashColor, formatTime, calculateMessageWidth } from '../helpers';
import { describe, it, expect, vi, beforeEach } from 'vitest';

describe('hashColor', () => {
  it('returns a consistent color for the same name', () => {
    expect(hashColor('Alice')).toBe(hashColor('Alice'));
    expect(hashColor('Bob')).toBe(hashColor('Bob'));
  });
  it('returns different colors for different names', () => {
    expect(hashColor('Alice')).not.toBe(hashColor('Bob'));
  });
  it('returns a valid hsl string', () => {
    expect(hashColor('Test')).toMatch(/^hsl\(\d+, 60%, 30%\)$/);
  });
});

describe('formatTime', () => {
  it('formats a unix timestamp as a time string', () => {
    const date = new Date('2024-01-01T12:34:56Z');
    const timestamp = Math.floor(date.getTime() / 1000);
    expect(typeof formatTime(timestamp)).toBe('string');
  });
});

describe('calculateMessageWidth', () => {
  it('returns a minimum width for short messages', () => {
    expect(calculateMessageWidth('Hi')).toBeGreaterThanOrEqual(60);
  });
  it('caps the width at 500 for long messages', () => {
    const longMsg = 'a'.repeat(200);
    expect(calculateMessageWidth(longMsg)).toBe(500);
  });
}); 