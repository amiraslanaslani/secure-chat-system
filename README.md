# Secure Encrypted Chat Application

A modern, secure chat application built with TypeScript that provides end-to-end encryption for messages. This is a personal project designed primarily for deployment on shared Linux hosts using an Apache web server. While it is not intended for large-scale use—since it relies on simple polling for message updates—it is completely secure thanks to client-side encryption. All encryption and decryption happen in the browser, ensuring that only users with the correct password can read the messages.

## Setup

### Prerequisites

- Node.js (v16 or higher)
- npm (or yarn)
- (Optional) PHP and PHPUnit for backend tests

### Installation & Build

## JS
1. Install dependencies:
   ```bash
   npm install
   composer install
   ```
2. Build the TypeScript and SCSS:
   ```bash
   npm run build
   ```
   Or, to build JS and CSS separately:
   ```bash
   npm run build:js
   npm run build:css
   ```
3. Start the development server:
   ```bash
   npm run dev
   ```
   This will build the project and serve it locally. By default, it uses the `serve` package to host the project directory.

## Usage Example

1. Open your browser and navigate to the address of web server.
2. You will see the chat interface. Enter your name, a password (used for encryption), and your message.
3. Click the send button (➤) to send your encrypted message to the channel.
4. Use the settings button to adjust the message fetch interval or switch channels.
5. All messages are encrypted in the browser and can only be read by users with the same channel and password.

**Sample Workflow:**
- Alice and Bob both open the app, select the same channel, and enter the same password.
- Alice sends a message; Bob sees the decrypted message in real time.
- Changing the password or channel will isolate conversations.

## Security

- Messages are encrypted using AES-GCM with 256-bit keys
- Keys are derived using PBKDF2 with 100,000 iterations
- Each message uses a unique salt and initialization vector
- Passwords are never stored in plain text
- All encryption/decryption happens in the browser

## Browser Support

The application uses modern web APIs including:
- Web Crypto API for encryption
- Fetch API for HTTP requests
- ES2020 modules
- LocalStorage for settings persistence

## Running Tests

To run backend unit tests (if applicable):

```
make test
```

## Security Notes
- Messages are encrypted in the browser with the password you provide.
- The server never sees the decrypted message or your password.
- Anyone with the same channel and password can read the messages.
- Passwords are not stored anywhere.
