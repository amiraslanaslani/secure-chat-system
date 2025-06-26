import { DOMService } from '../dom.service';
import { describe, it, expect, vi, beforeEach } from 'vitest';

describe('DOMService', () => {
  let elements: any;
  let service: DOMService;

  beforeEach(() => {
    elements = {
      messagesDiv: { innerHTML: '', scrollTop: 0, scrollHeight: 100, appendChild: vi.fn() },
      nameInput: { value: 'Alice', style: { display: '' } },
      passwordInput: { value: 'pw', style: { display: '' } },
      messageInput: { value: 'hi' },
      sendButton: { innerHTML: '' },
      channelNameSpan: { textContent: '' },
      settingsModal: { style: { display: '' } },
      intervalInput: { value: '1000' },
      channelInput: { value: 'chan' },
      chatTrigger: { classList: { contains: vi.fn(), add: vi.fn(), remove: vi.fn() }, style: { display: '' } },
      passwordModal: { style: { display: '' } },
      passwordModalInput: { value: '', focus: vi.fn() },
      passwordModalConfirm: { addEventListener: vi.fn(), removeEventListener: vi.fn() },
      passwordModalCancel: { addEventListener: vi.fn(), removeEventListener: vi.fn() },
      passwordModalError: { style: { display: '' } }
    };
    service = new DOMService(elements);
  });

  it('clearMessages sets messagesDiv.innerHTML to empty', () => {
    elements.messagesDiv.innerHTML = 'something';
    service.clearMessages();
    expect(elements.messagesDiv.innerHTML).toBe('');
  });

  it('scrollToBottom sets scrollTop to scrollHeight', () => {
    elements.messagesDiv.scrollTop = 0;
    elements.messagesDiv.scrollHeight = 123;
    service.scrollToBottom();
    expect(elements.messagesDiv.scrollTop).toBe(123);
  });

  it('updateChannelName sets channelNameSpan.textContent', () => {
    service.updateChannelName('chan');
    expect(elements.channelNameSpan.textContent).toBe('#chan');
  });

  it('setSendButtonLoading sets sendButton.innerHTML', () => {
    service.setSendButtonLoading(true);
    expect(elements.sendButton.innerHTML).toBe('\u23f3');
    service.setSendButtonLoading(false);
    expect(elements.sendButton.innerHTML).toBe('\u27a4');
  });

  it('clearMessageInput sets messageInput.value to empty', () => {
    elements.messageInput.value = 'hi';
    service.clearMessageInput();
    expect(elements.messageInput.value).toBe('');
  });

  it('updateSettingsInputs sets intervalInput and channelInput values', () => {
    service.updateSettingsInputs(1234, 'chan2');
    expect(elements.intervalInput.value).toBe('1234');
    expect(elements.channelInput.value).toBe('chan2');
  });

  it('toggleChatTrigger toggles open/close classes and input display', () => {
    elements.chatTrigger.classList.contains.mockImplementation((cls: string) => cls === 'open');
    service.toggleChatTrigger();
    expect(elements.chatTrigger.classList.remove).toHaveBeenCalledWith('open');
    expect(elements.chatTrigger.classList.add).toHaveBeenCalledWith('close');
    elements.chatTrigger.classList.contains.mockImplementation((cls: string) => cls === 'close');
    service.toggleChatTrigger();
    expect(elements.chatTrigger.classList.remove).toHaveBeenCalledWith('close');
    expect(elements.chatTrigger.classList.add).toHaveBeenCalledWith('open');
  });

  it('getFormData returns trimmed values', () => {
    elements.nameInput.value = ' Alice ';
    elements.passwordInput.value = 'pw';
    elements.messageInput.value = ' hi ';
    expect(service.getFormData()).toEqual({ name: 'Alice', password: 'pw', message: 'hi' });
  });

  it('getSettingsFormData returns correct values', () => {
    elements.intervalInput.value = '1234';
    elements.channelInput.value = 'chan';
    expect(service.getSettingsFormData()).toEqual({ messageInterval: 1234, channel: 'chan' });
  });
}); 