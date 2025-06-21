import { ChatApp } from './app.js';

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  try {
    const app = new ChatApp();
    app.initialize();
    console.log('Chat application initialized successfully');
  } catch (error) {
    console.error('Failed to initialize chat application:', error);
  }
}); 