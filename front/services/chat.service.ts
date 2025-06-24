import { ChatMessage, SendMessagePayload } from '../types/index.js';
import { ApiService, ApiError } from './api.service.js';
import { DOMService } from './dom.service.js';
import { SettingsService } from './settings.service.js';
import { EncryptionService } from './encryption.service.js';
import { VaultService } from './vault.service.js';

export class ChatService {
  private domService: DOMService;
  private lastIndex: number = 0;
  private encryptedMessages: ChatMessage[] = [];
  private fetchIntervalId: number | null = null;
  private passwordChangeTimer: number | null = null;
  private msgSendLock: boolean = false;

  constructor(domService: DOMService) {
    this.domService = domService;
  }

  async resetAndFetchMessages(): Promise<void> {
    this.lastIndex = 0;
    this.encryptedMessages = [];
    this.domService.clearMessages();
    await this.checkForPassword();
    await this.fetchMessages();
  }

  async fetchMessages(): Promise<void> {
    try {
      const data = await ApiService.fetchMessages(this.lastIndex, SettingsService.channel);
      
      if (data.length > 0) {
        for (const entry of data) {
          this.encryptedMessages.push(entry);
          const formData = this.domService.getFormData();
          await this.domService.renderMessage(entry, formData.name, formData.password);
        }
        this.lastIndex += data.length;
        this.domService.scrollToBottom();
      }
    } catch (error) {
      console.error('Fetch error:', error);
    }
  }

  async checkForPassword(): Promise<void> {
    let isNeedAuth = await ApiService.isNeedAuth(SettingsService.channel);
    if(isNeedAuth) {
      let password = await this.domService.getChannelPasswordFromUser();
      if(password){
        if(await ApiService.isAuthCorrect(SettingsService.channel, password)){
          await VaultService.setPassword(SettingsService.channel, password);
        } else {
          this.checkForPassword();
        }
      }
    }
  }

  async retryDecryptAllMessages(): Promise<void> {
    this.domService.clearMessages();
    const formData = this.domService.getFormData();
    
    for (const entry of this.encryptedMessages) {
      await this.domService.renderMessage(entry, formData.name, formData.password);
    }
    this.domService.scrollToBottom();
  }

  restartFetchInterval(): void {
    if (this.fetchIntervalId) {
      clearInterval(this.fetchIntervalId);
    }
    this.resetAndFetchMessages();
    this.fetchIntervalId = window.setInterval(
      () => this.fetchMessages(),
      SettingsService.messageInterval
    );
  }

  async sendMessage(event: Event): Promise<void> {
    event.preventDefault();
    
    if (this.msgSendLock) return;
    
    this.msgSendLock = true;
    const formData = this.domService.getFormData();
    
    if (!formData.name || !formData.password || !formData.message) {
      this.msgSendLock = false;
      return;
    }

    this.domService.setSendButtonLoading(true);
    
    // Save to localStorage
    SettingsService.rememberedName = formData.name;
    SettingsService.rememberedPassword = formData.password;

    try {
      const encrypted = await EncryptionService.encrypt(formData.password, formData.message);
      const payload: SendMessagePayload = {
        name: formData.name,
        message: encrypted,
        channel: SettingsService.channel
      };

      const result = await ApiService.sendMessage(payload);
      
      this.domService.setSendButtonLoading(false);
      this.msgSendLock = false;

      if (result.success) {
        this.domService.clearMessageInput();
        await this.fetchMessages();
      } else {
        alert(result.error || 'Failed to send message.');
      }
    } catch (error) {
      this.msgSendLock = false;
      this.domService.setSendButtonLoading(false);

      if (error instanceof ApiError){
        if(error.status == 401) {
          this.checkForPassword();
          return
        }
      }

      alert('Error sending message.');
      console.error(error);
    }
  }

  handlePasswordChange(): void {
    if (this.passwordChangeTimer) {
      clearTimeout(this.passwordChangeTimer);
    }
    this.passwordChangeTimer = window.setTimeout(
      () => this.retryDecryptAllMessages(),
      3000
    );
  }

  handleSettingsSubmit(event: Event): void {
    event.preventDefault();
    const settingsData = this.domService.getSettingsFormData();
    
    SettingsService.updateSettings(settingsData);
    this.domService.hideSettingsModal();
    this.domService.updateChannelName(SettingsService.channel);
    this.restartFetchInterval();
  }

  initialize(): void {
    // Set remembered values
    const rememberedName = SettingsService.rememberedName;
    if (rememberedName) {
      this.domService.nameInput.value = rememberedName;
    }
    
    const rememberedPassword = SettingsService.rememberedPassword;
    if (rememberedPassword) {
      this.domService.passwordInput.value = rememberedPassword;
    }

    // Update UI with current settings
    const settings = SettingsService.getSettings();
    this.domService.updateSettingsInputs(settings.messageInterval, settings.channel);
    this.domService.updateChannelName(settings.channel);

    // Start fetching messages
    this.restartFetchInterval();
  }

  cleanup(): void {
    if (this.fetchIntervalId) {
      clearInterval(this.fetchIntervalId);
    }
    if (this.passwordChangeTimer) {
      clearTimeout(this.passwordChangeTimer);
    }
  }
} 