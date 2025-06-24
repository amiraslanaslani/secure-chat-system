import { ChatService } from '../chat.service';

const mockDomService = {
  clearMessages: jest.fn(),
  getFormData: jest.fn(() => ({ name: 'Alice', password: 'pw', message: 'hi' })),
  renderMessage: jest.fn(),
  scrollToBottom: jest.fn(),
  setSendButtonLoading: jest.fn(),
  clearMessageInput: jest.fn(),
  getSettingsFormData: jest.fn(() => ({ messageInterval: 1000, channel: 'chan' })),
  hideSettingsModal: jest.fn(),
  updateChannelName: jest.fn(),
  updateSettingsInputs: jest.fn(),
  nameInput: { value: '' },
  passwordInput: { value: '' },
  messageInput: { value: '' }
};

jest.mock('../api.service', () => ({
  ApiService: {
    fetchMessages: jest.fn(() => Promise.resolve([])),
    isNeedAuth: jest.fn(() => Promise.resolve(false)),
    isAuthCorrect: jest.fn(() => Promise.resolve(true)),
    sendMessage: jest.fn(() => Promise.resolve({ success: true }))
  },
  ApiError: class extends Error { constructor(msg: string, public status: number) { super(msg); } }
}));

jest.mock('../settings.service', () => ({
  SettingsService: {
    channel: 'chan',
    messageInterval: 1000,
    rememberedName: 'Alice',
    rememberedPassword: 'pw',
    getSettings: jest.fn(() => ({ messageInterval: 1000, channel: 'chan' })),
    updateSettings: jest.fn()
  }
}));

jest.mock('../encryption.service', () => ({
  EncryptionService: {
    encrypt: jest.fn(() => Promise.resolve('encrypted'))
  }
}));

jest.mock('../vault.service', () => ({
  VaultService: {
    setPassword: jest.fn()
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
    jest.clearAllMocks();
    chat = new ChatService(mockDomService as any);
  });

  afterEach(() => {
    chat.cleanup();
  });

  it('constructs with a domService', () => {
    expect(chat).toBeInstanceOf(ChatService);
  });

  it('restartFetchInterval clears and sets interval', () => {
    jest.useFakeTimers();
    // @ts-ignore
    chat.fetchIntervalId = setInterval(() => {}, 1000);
    chat.resetAndFetchMessages = jest.fn();
    chat.fetchMessages = jest.fn();
    chat.restartFetchInterval();
    expect(chat.resetAndFetchMessages).toHaveBeenCalled();
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
  });

  it('sendMessage basic flow', async () => {
    const event = { preventDefault: jest.fn() } as any;
    await chat.sendMessage(event);
    expect(mockDomService.setSendButtonLoading).toHaveBeenCalledWith(true);
    expect(mockDomService.clearMessageInput).toHaveBeenCalled();
  });

  it('cleanup clears intervals and timers', () => {
    jest.useFakeTimers();
    // @ts-ignore
    chat.fetchIntervalId = setInterval(() => {}, 1000);
    // @ts-ignore
    chat.passwordChangeTimer = setTimeout(() => {}, 1000);
    chat.cleanup();
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
  });
}); 