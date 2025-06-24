import { ChatMessage, SendMessagePayload, ApiResponse } from '../types/index';
import { VaultService } from './vault.service';

export class ApiError extends Error {
  status: number;

  constructor(message: string, status: number) {
    super(message);
    this.status = status;
  }
}

export class ApiService {
  private static readonly API_READ_URL = 'apis/chat/read';
  private static readonly API_WRITE_URL = 'apis/chat/send';

  static async isNeedAuth(channel: string): Promise<boolean> {
    let url = `${this.API_READ_URL}?from=0&channel=${channel}`;
    const response = await fetch(url);
    if (response.status == 401) {
      return true;
    }
    return false;
  }

  static async isAuthCorrect(channel: string, password: string): Promise<boolean> {
    let url = `${this.API_READ_URL}?from=0&channel=${encodeURIComponent(channel)}`;
    const response = await fetch(url, {headers: {"Authorization": "Bearer " + btoa(password)}});
    if (response.status == 401) {
      return false;
    }
    return true;
  }

  static async fetchMessages(fromIndex: number, channel: string): Promise<ChatMessage[]> {
    try {
      let url = `${this.API_READ_URL}?from=${fromIndex}`;
      if (channel) {
        url += `&channel=${encodeURIComponent(channel)}`;
      }

      const headers: Record<string, string> = {};
      let password = await VaultService.getPassword(channel);
      if (password) {
        headers['Authorization'] = `Bearer ${btoa(password)}`;
      }

      const response = await fetch(url, { headers: headers });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      return Array.isArray(data) ? data : [];
    } catch (error) {
      console.error('Fetch messages error:', error);
      throw new Error(`Failed to fetch messages: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  static async sendMessage(payload: SendMessagePayload): Promise<ApiResponse> {
    try {
      const headers: Record<string, string> = {
        'Content-Type': 'application/json'
      };
      let password = await VaultService.getPassword(payload.channel);
      if (password) {
        headers['Authorization'] = `Bearer ${btoa(password)}`;
      }

      const response = await fetch(this.API_WRITE_URL, {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        throw new ApiError(`HTTP error! status: ${response.status}`, response.status);
      }

      return await response.json();
    } catch (error) {
      console.error('Send message error:', error);
      if (error instanceof ApiError) {
        throw error;
      }
      throw new Error(`Failed to send message: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }
} 
