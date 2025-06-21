export interface ChatMessage {
  name: string;
  message: string;
  timestamp: number;
  channel?: string;
}

export interface SendMessagePayload {
  name: string;
  message: string;
  channel: string;
}

export interface ApiResponse {
  success: boolean;
  error?: string;
}

export interface Settings {
  messageInterval: number;
  channel: string;
}

export interface DOMElements {
  messagesDiv: HTMLElement;
  form: HTMLFormElement;
  nameInput: HTMLInputElement;
  messageInput: HTMLInputElement;
  passwordInput: HTMLInputElement;
  sendButton: HTMLButtonElement;
  channelNameSpan: HTMLElement;
  settingsBtn: HTMLElement;
  settingsModal: HTMLElement;
  settingsForm: HTMLFormElement;
  closeSettings: HTMLElement;
  intervalInput: HTMLInputElement;
  channelInput: HTMLInputElement;
  chatTrigger?: HTMLElement;
} 