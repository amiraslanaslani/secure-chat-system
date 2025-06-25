import { Settings } from '../types/index';

export class SettingsService {
  private static readonly STORAGE_KEYS = {
    MESSAGE_INTERVAL: 'messageInterval',
    CHANNEL: 'channel',
    REMEMBERED_NAME: 'rememberedName',
    REMEMBERED_PASSWORD: 'rememberedPassword'
  } as const;

  private static getLocal(key: string): string | null {
    return localStorage.getItem(key);
  }

  private static setLocal(key: string, value: string): void {
    localStorage.setItem(key, value);
  }

  static get messageInterval(): number {
    return parseInt(this.getLocal(this.STORAGE_KEYS.MESSAGE_INTERVAL) || '1000');
  }

  static set messageInterval(value: number) {
    this.setLocal(this.STORAGE_KEYS.MESSAGE_INTERVAL, value.toString());
  }

  static get channel(): string {
    return this.getLocal(this.STORAGE_KEYS.CHANNEL) || 'default';
  }

  static set channel(value: string) {
    this.setLocal(this.STORAGE_KEYS.CHANNEL, value);
  }

  static get rememberedName(): string | null {
    return this.getLocal(this.STORAGE_KEYS.REMEMBERED_NAME);
  }

  static set rememberedName(value: string) {
    this.setLocal(this.STORAGE_KEYS.REMEMBERED_NAME, value);
  }

  static get rememberedPassword(): string | null {
    return this.getLocal(this.STORAGE_KEYS.REMEMBERED_PASSWORD);
  }

  static set rememberedPassword(value: string) {
    this.setLocal(this.STORAGE_KEYS.REMEMBERED_PASSWORD, value);
  }

  static getSettings(): Settings {
    return {
      messageInterval: this.messageInterval,
      channel: this.channel
    };
  }

  static updateSettings(settings: Partial<Settings>): void {
    if (settings.messageInterval !== undefined) {
      this.messageInterval = settings.messageInterval;
    }
    if (settings.channel !== undefined) {
      this.channel = settings.channel;
    }
  }
} 