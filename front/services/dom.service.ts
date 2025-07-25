import { DOMElements } from '../types/index';
import { hashColor, formatTime, calculateMessageWidth } from '../utils/helpers';
import { EncryptionService } from './encryption.service';

export class DOMService {
  private elements: DOMElements;

  constructor(elements: DOMElements) {
    this.elements = elements;
  }

  // Public getters for elements that need to be accessed externally
  get nameInput(): HTMLInputElement {
    return this.elements.nameInput;
  }

  get passwordInput(): HTMLInputElement {
    return this.elements.passwordInput;
  }

  async renderMessage(entry: any, currentUser: string, password: string, append: boolean = true): Promise<void> {
    const row = document.createElement('div');
    row.className = 'message-row';
    
    const isUser = entry.name === currentUser;
    const div = document.createElement('div');

    try {
      const decrypted = await EncryptionService.decrypt(password, entry.message);
      div.className = `message ${isUser ? 'user' : 'other'}`;
      div.style.width = `${calculateMessageWidth(decrypted)}px`;
      div.innerHTML = `
        <span class="name" style="color: ${hashColor(entry.name)}">${entry.name}</span>
        <div>${decrypted}</div>
        <span class="time">${formatTime(entry.timestamp)}</span>
      `;
    } catch {
      div.className = 'message other';
      div.innerHTML = `
        <span class="name" style="color: ${hashColor(entry.name)}">${entry.name}</span>
        <div style="color: red;">[Unable to decrypt message]</div>
        <span class="time">${formatTime(entry.timestamp)}</span>
      `;
    }

    row.style.justifyContent = isUser ? 'flex-end' : 'flex-start';
    row.appendChild(div);
    
    if (append) {
      this.elements.messagesDiv.appendChild(row);
    }
  }

  clearMessages(): void {
    this.elements.messagesDiv.innerHTML = '';
  }

  scrollToBottom(): void {
    this.elements.messagesDiv.scrollTop = this.elements.messagesDiv.scrollHeight;
  }

  updateChannelName(channel: string): void {
    this.elements.channelNameSpan.textContent = `#${channel || 'default'}`;
  }

  setSendButtonLoading(loading: boolean): void {
    this.elements.sendButton.innerHTML = loading ? '⏳' : '➤';
  }

  clearMessageInput(): void {
    this.elements.messageInput.value = '';
  }

  showSettingsModal(): void {
    this.elements.settingsModal.style.display = 'flex';
  }

  hideSettingsModal(): void {
    this.elements.settingsModal.style.display = 'none';
  }

  updateSettingsInputs(messageInterval: number, channel: string): void {
    this.elements.intervalInput.value = messageInterval.toString();
    this.elements.channelInput.value = channel;
  }

  toggleChatTrigger(): void {
    if (!this.elements.chatTrigger) return;

    if (this.elements.chatTrigger.classList.contains('open')) {
      this.elements.chatTrigger.classList.remove('open');
      this.elements.chatTrigger.classList.add('close');
      this.elements.nameInput.style.display = 'none';
      this.elements.passwordInput.style.display = 'none';
    } else if (this.elements.chatTrigger.classList.contains('close')) {
      this.elements.chatTrigger.classList.remove('close');
      this.elements.chatTrigger.classList.add('open');
      this.elements.nameInput.style.display = '';
      this.elements.passwordInput.style.display = '';
    }
  }

  getFormData(): { name: string; password: string; message: string } {
    return {
      name: this.elements.nameInput.value.trim(),
      password: this.elements.passwordInput.value,
      message: this.elements.messageInput.value.trim()
    };
  }

  getSettingsFormData(): { messageInterval: number; channel: string } {
    return {
      messageInterval: parseInt(this.elements.intervalInput.value),
      channel: this.elements.channelInput.value.trim() || 'default'
    };
  }

  getChannelPasswordFromUser(): Promise<string | null> {
    return new Promise((resolve) => {
      const modal = this.elements.passwordModal;
      const input = this.elements.passwordModalInput;
      const confirmBtn = this.elements.passwordModalConfirm;
      const cancelBtn = this.elements.passwordModalCancel;
      const errorDiv = this.elements.passwordModalError;

      input.value = '';
      errorDiv.style.display = 'none';
      modal.style.display = 'flex';
      input.focus();

      const onConfirm = () => {
        const value = input.value.trim();
        if (!value) {
          errorDiv.style.display = 'block';
          return;
        }
        cleanup();
        modal.style.display = 'none';
        resolve(value);
      };

      const onCancel = () => {
        cleanup();
        modal.style.display = 'none';
        resolve(null);
      };

      function cleanup() {
        confirmBtn.removeEventListener('click', onConfirm);
        cancelBtn.removeEventListener('click', onCancel);
        input.removeEventListener('keydown', onKeyDown);
      }

      function onKeyDown(e: KeyboardEvent) {
        if (e.key === 'Enter') {
          onConfirm();
        } else if (e.key === 'Escape') {
          onCancel();
        }
      }

      confirmBtn.addEventListener('click', onConfirm);
      cancelBtn.addEventListener('click', onCancel);
      input.addEventListener('keydown', onKeyDown);
    });
  }
}
