import { EncryptionService } from '../encryption.service';

global.btoa = (str) => Buffer.from(str, 'binary').toString('base64');
global.atob = (str) => Buffer.from(str, 'base64').toString('binary');

describe('EncryptionService', () => {
  const password = 'testpass';
  const message = 'hello world';

  beforeAll(() => {
    // Mock crypto.subtle for deterministic tests
    global.crypto = {
      getRandomValues: (arr: Uint8Array) => {
        for (let i = 0; i < arr.length; i++) arr[i] = i;
        return arr;
      },
      subtle: {
        importKey: jest.fn().mockResolvedValue('keyMaterial'),
        deriveKey: jest.fn().mockResolvedValue('cryptoKey'),
        encrypt: jest.fn().mockResolvedValue(new Uint8Array([1,2,3,4]).buffer),
        decrypt: jest.fn().mockResolvedValue(new TextEncoder().encode(message).buffer),
        deriveBits: jest.fn(),
        digest: jest.fn(),
        exportKey: jest.fn(),
        generateKey: jest.fn(),
        sign: jest.fn(),
        unwrapKey: jest.fn(),
        verify: jest.fn(),
        wrapKey: jest.fn()
      } as unknown as SubtleCrypto
    } as unknown as Crypto;
  });

  it('encrypts and decrypts a message (round-trip)', async () => {
    const encrypted = await EncryptionService.encrypt(password, message);
    const decrypted = await EncryptionService.decrypt(password, encrypted);
    expect(decrypted).toBe(message);
  });

  it('throws on encryption error', async () => {
    (global.crypto.subtle.encrypt as jest.Mock).mockRejectedValueOnce(new Error('fail'));
    await expect(EncryptionService.encrypt(password, message)).rejects.toThrow('Encryption failed');
  });

  it('throws on decryption error', async () => {
    (global.crypto.subtle.decrypt as jest.Mock).mockRejectedValueOnce(new Error('fail'));
    // Use a valid encrypted string for the test
    const encrypted = await EncryptionService.encrypt(password, message);
    await expect(EncryptionService.decrypt(password, encrypted)).rejects.toThrow('Decryption failed');
  });
}); 