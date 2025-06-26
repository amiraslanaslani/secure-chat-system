import { describe, it, expect, vi, beforeEach } from 'vitest';
import { ApiService } from '../api.service';

global.fetch = vi.fn();

vi.mock('../vault.service');

const mockFetch = global.fetch as any;

beforeEach(() => {
  vi.clearAllMocks();
});

describe('ApiService', () => {
  describe('isNeedAuth', () => {
    it('returns true if response status is 401', async () => {
      mockFetch.mockResolvedValueOnce({ status: 401 });
      const result = await ApiService.isNeedAuth('test-channel');
      expect(result).toBe(true);
    });
    it('returns false if response status is not 401', async () => {
      mockFetch.mockResolvedValueOnce({ status: 200 });
      const result = await ApiService.isNeedAuth('test-channel');
      expect(result).toBe(false);
    });
  });

  describe('isAuthCorrect', () => {
    it('returns false if response status is 401', async () => {
      mockFetch.mockResolvedValueOnce({ status: 401 });
      const result = await ApiService.isAuthCorrect('test-channel', 'password');
      expect(result).toBe(false);
    });
    it('returns true if response status is not 401', async () => {
      mockFetch.mockResolvedValueOnce({ status: 200 });
      const result = await ApiService.isAuthCorrect('test-channel', 'password');
      expect(result).toBe(true);
    });
  });
}); 