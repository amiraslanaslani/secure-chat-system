import { EncryptionService } from '../encryption.service';
import { describe, it, expect, vi, beforeEach, beforeAll, MockInstance } from 'vitest';

global.btoa = (str) => Buffer.from(str, 'binary').toString('base64');
global.atob = (str) => Buffer.from(str, 'base64').toString('binary');

describe('EncryptionService', () => {
  const password = 'testpass';
  const message = 'hello world';

  beforeAll(() => {
    // Mock crypto.subtle for deterministic tests
    vi.stubGlobal('crypto', {
      getRandomValues: (arr: Uint8Array) => {
        for (let i = 0; i < arr.length; i++) arr[i] = i;
        return arr;
      },
      subtle: {
        importKey: vi.fn().mockResolvedValue('keyMaterial'),
        deriveKey: vi.fn().mockResolvedValue('cryptoKey'),
        encrypt: vi.fn().mockResolvedValue(new Uint8Array([1,2,3,4]).buffer),
        decrypt: vi.fn().mockResolvedValue(new TextEncoder().encode(message).buffer),
        deriveBits: vi.fn(),
        digest: vi.fn(),
        exportKey: vi.fn(),
        generateKey: vi.fn(),
        sign: vi.fn(),
        unwrapKey: vi.fn(),
        verify: vi.fn(),
        wrapKey: vi.fn()
      } as unknown as SubtleCrypto
    } as unknown as Crypto);
  });

  it('encrypts and decrypts a message (round-trip)', async () => {
    const encrypted = await EncryptionService.encrypt(password, message);
    const decrypted = await EncryptionService.decrypt(password, encrypted);
    expect(decrypted).toBe(message);
  });

  it('throws on encryption error', async () => {
    (global.crypto.subtle.encrypt as unknown as MockInstance).mockRejectedValueOnce(new Error('fail'));
    await expect(EncryptionService.encrypt(password, message)).rejects.toThrow('Encryption failed');
  });

  it('throws on decryption error', async () => {
    (global.crypto.subtle.decrypt as unknown as MockInstance).mockRejectedValueOnce(new Error('fail'));
    // Use a valid encrypted string for the test
    const encrypted = await EncryptionService.encrypt(password, message);
    await expect(EncryptionService.decrypt(password, encrypted)).rejects.toThrow('Decryption failed');
  });
}); 