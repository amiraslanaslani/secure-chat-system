import { VaultService } from '../vault.service';

describe('VaultService', () => {
  beforeEach(() => {
    VaultService.channelPasswords = new Map();
  });

  it('returns undefined for unknown channel', async () => {
    expect(await VaultService.getPassword('unknown')).toBeUndefined();
  });

  it('can set and get a password for a channel', async () => {
    await VaultService.setPassword('chan', 'secret');
    expect(await VaultService.getPassword('chan')).toBe('secret');
  });

  it('overwrites password for the same channel', async () => {
    await VaultService.setPassword('chan', 'first');
    await VaultService.setPassword('chan', 'second');
    expect(await VaultService.getPassword('chan')).toBe('second');
  });
}); 