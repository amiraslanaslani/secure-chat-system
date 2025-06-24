import { SettingsService } from '../settings.service';

describe('SettingsService', () => {
  let store: Record<string, string> = {};
  beforeEach(() => {
    store = {};
    global.localStorage = {
      getItem: jest.fn((key: string) => store[key] || null),
      setItem: jest.fn((key: string, value: string) => { store[key] = value; }),
      removeItem: jest.fn((key: string) => { delete store[key]; }),
      clear: jest.fn(() => { store = {}; }),
      key: jest.fn((index: number) => Object.keys(store)[index] || null),
      get length() { return Object.keys(store).length; }
    } as unknown as Storage;
  });

  it('gets and sets messageInterval', () => {
    SettingsService.messageInterval = 2000;
    expect(SettingsService.messageInterval).toBe(2000);
  });

  it('gets and sets channel', () => {
    SettingsService.channel = 'testchan';
    expect(SettingsService.channel).toBe('testchan');
  });

  it('gets and sets rememberedName', () => {
    SettingsService.rememberedName = 'Alice';
    expect(SettingsService.rememberedName).toBe('Alice');
  });

  it('gets and sets rememberedPassword', () => {
    SettingsService.rememberedPassword = 'secret';
    expect(SettingsService.rememberedPassword).toBe('secret');
  });

  it('getSettings returns correct values', () => {
    SettingsService.messageInterval = 1234;
    SettingsService.channel = 'chan';
    expect(SettingsService.getSettings()).toEqual({ messageInterval: 1234, channel: 'chan' });
  });

  it('updateSettings updates only provided fields', () => {
    SettingsService.messageInterval = 1000;
    SettingsService.channel = 'chan';
    SettingsService.updateSettings({ messageInterval: 4321 });
    expect(SettingsService.messageInterval).toBe(4321);
    expect(SettingsService.channel).toBe('chan');
    SettingsService.updateSettings({ channel: 'newchan' });
    expect(SettingsService.channel).toBe('newchan');
  });
}); 