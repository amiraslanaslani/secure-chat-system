import { describe, it, expect, vi, beforeEach, afterEach, beforeAll } from 'vitest';
import { ChatService } from '../chat.service';

const mockDomService = {
  clearMessages: vi.fn(),
  getFormData: vi.fn(() => ({ name: 'Alice', password: 'pw', message: 'hi' })),
  renderMessage: vi.fn(),
  scrollToBottom: vi.fn(),
  setSendButtonLoading: vi.fn(),
  clearMessageInput: vi.fn(),
  getSettingsFormData: vi.fn(() => ({ messageInterval: 1000, channel: 'chan' })),
  hideSettingsModal: vi.fn(),
  updateChannelName: vi.fn(),
  updateSettingsInputs: vi.fn(),
  nameInput: { value: '' },
  passwordInput: { value: '' },
  messageInput: { value: '' }
};

vi.mock('../api.service', () => ({
  ApiService: {
    fetchMessages: vi.fn(() => Promise.resolve([])),
    isNeedAuth: vi.fn(() => Promise.resolve(false)),
    isAuthCorrect: vi.fn(() => Promise.resolve(true)),
    sendMessage: vi.fn(() => Promise.resolve({ success: true }))
  },
  ApiError: class extends Error { constructor(msg: string, public status: number) { super(msg); } }
}));

vi.mock('../settings.service', () => ({
  SettingsService: {
    channel: 'chan',
    messageInterval: 1000,
    rememberedName: 'Alice',
    rememberedPassword: 'pw',
    getSettings: vi.fn(() => ({ messageInterval: 1000, channel: 'chan' })),
    updateSettings: vi.fn()
  }
}));

vi.mock('../encryption.service', () => ({
  EncryptionService: {
    encrypt: vi.fn(() => Promise.resolve('encrypted'))
  }
}));

vi.mock('../vault.service', () => ({
  VaultService: {
    setPassword: vi.fn()
  }
}));

describe('ChatService', () => {
  let chat: ChatService;

  beforeAll(() => {
    // @ts-ignore
    global.window = Object.create(global);
    window.setInterval = setInterval;
    window.clearInterval = clearInterval;
    window.setTimeout = setTimeout;
    window.clearTimeout = clearTimeout;
  });

  beforeEach(() => {
    vi.clearAllMocks();
    chat = new ChatService(mockDomService as any);
  });

  afterEach(() => {
    chat.cleanup();
  });

  it('constructs with a domService', () => {
    expect(chat).toBeInstanceOf(ChatService);
  });

  it('restartFetchInterval clears and sets interval', () => {
    vi.useFakeTimers();
    // @ts-ignore
    chat.fetchIntervalId = setInterval(() => {}, 1000);
    chat.resetAndFetchMessages = vi.fn();
    chat.fetchMessages = vi.fn();
    chat.restartFetchInterval();
    expect(chat.resetAndFetchMessages).toHaveBeenCalled();
    vi.runOnlyPendingTimers();
    vi.useRealTimers();
  });

  it('sendMessage basic flow', async () => {
    const event = { preventDefault: vi.fn() } as any;
    await chat.sendMessage(event);
    expect(mockDomService.setSendButtonLoading).toHaveBeenCalledWith(true);
    expect(mockDomService.clearMessageInput).toHaveBeenCalled();
  });

  it('cleanup clears intervals and timers', () => {
    vi.useFakeTimers();
    // @ts-ignore
    chat.fetchIntervalId = setInterval(() => {}, 1000);
    // @ts-ignore
    chat.passwordChangeTimer = setTimeout(() => {}, 1000);
    chat.cleanup();
    vi.runOnlyPendingTimers();
    vi.useRealTimers();
  });
}); 