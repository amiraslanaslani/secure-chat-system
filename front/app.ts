import { DOMElements } from './types/index.js';
import { DOMService } from './services/dom.service.js';
import { ChatService } from './services/chat.service.js';

export class ChatApp {
  private domService: DOMService;
  private chatService: ChatService;

  constructor() {
    this.initializeDOMElements();
    this.domService = new DOMService(this.getDOMElements());
    this.chatService = new ChatService(this.domService);
  }

  private initializeDOMElements(): void {
    // Ensure all required DOM elements exist
    const requiredElements = [
      'messages', 'chatForm', 'name', 'message', 'password', 'sendButton',
      'channelName', 'settingsBtn', 'settingsModal', 'settingsForm',
      'closeSettings', 'interval', 'channel'
    ];

    for (const id of requiredElements) {
      if (!document.getElementById(id)) {
        throw new Error(`Required DOM element with id '${id}' not found`);
      }
    }
  }

  private getDOMElements(): DOMElements {
    return {
      messagesDiv: document.getElementById('messages') as HTMLElement,
      form: document.getElementById('chatForm') as HTMLFormElement,
      nameInput: document.getElementById('name') as HTMLInputElement,
      messageInput: document.getElementById('message') as HTMLInputElement,
      passwordInput: document.getElementById('password') as HTMLInputElement,
      sendButton: document.getElementById('sendButton') as HTMLButtonElement,
      channelNameSpan: document.getElementById('channelName') as HTMLElement,
      settingsBtn: document.getElementById('settingsBtn') as HTMLElement,
      settingsModal: document.getElementById('settingsModal') as HTMLElement,
      settingsForm: document.getElementById('settingsForm') as HTMLFormElement,
      closeSettings: document.getElementById('closeSettings') as HTMLElement,
      intervalInput: document.getElementById('interval') as HTMLInputElement,
      channelInput: document.getElementById('channel') as HTMLInputElement,
      chatTrigger: document.getElementById('chat-trigger') as HTMLElement || undefined
    };
  }

  private setupEventListeners(): void {
    const elements = this.getDOMElements();

    // Settings modal events
    elements.settingsBtn.addEventListener('click', () => {
      this.domService.showSettingsModal();
      const settings = this.domService.getSettingsFormData();
      this.domService.updateSettingsInputs(settings.messageInterval, settings.channel);
    });

    elements.closeSettings.addEventListener('click', () => {
      this.domService.hideSettingsModal();
    });

    elements.settingsForm.addEventListener('submit', (e) => {
      this.chatService.handleSettingsSubmit(e);
    });

    // Password input event
    elements.passwordInput.addEventListener('input', () => {
      this.chatService.handlePasswordChange();
    });

    // Form submit event
    elements.form.addEventListener('submit', (e) => {
      this.chatService.sendMessage(e);
    });

    // Chat trigger event
    if (elements.chatTrigger) {
      elements.chatTrigger.addEventListener('click', () => {
        this.domService.toggleChatTrigger();
      });
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
      this.chatService.cleanup();
    });
  }

  initialize(): void {
    this.setupEventListeners();
    this.chatService.initialize();
  }
} 