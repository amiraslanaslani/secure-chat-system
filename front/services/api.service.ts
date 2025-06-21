import { ChatMessage, SendMessagePayload, ApiResponse } from '../types/index.js';

export class ApiService {
  private static readonly API_READ_URL = 'apis/chat/read';
  private static readonly API_WRITE_URL = 'apis/chat/send';

  static async fetchMessages(fromIndex: number, channel?: string): Promise<ChatMessage[]> {
    try {
      let url = `${this.API_READ_URL}?from=${fromIndex}`;
      if (channel) {
        url += `&channel=${encodeURIComponent(channel)}`;
      }

      const response = await fetch(url);
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
      const response = await fetch(this.API_WRITE_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error('Send message error:', error);
      throw new Error(`Failed to send message: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }
} 
