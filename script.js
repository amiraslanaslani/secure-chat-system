// --- DOM Elements ---
const messagesDiv = document.getElementById('messages');
const form = document.getElementById('chatForm');
const nameInput = document.getElementById('name');
const messageInput = document.getElementById('message');
const passwordInput = document.getElementById('password');
const sendButton = document.getElementById('sendButton');
const channelNameSpan = document.getElementById('channelName');
const settingsBtn = document.getElementById('settingsBtn');
const settingsModal = document.getElementById('settingsModal');
const settingsForm = document.getElementById('settingsForm');
const closeSettings = document.getElementById('closeSettings');
const intervalInput = document.getElementById('interval');
const channelInput = document.getElementById('channel');

// --- Helper Functions ---
const getLocal = key => localStorage.getItem(key);
const setLocal = (key, value) => localStorage.setItem(key, value);
const hashColor = name => {
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  const hue = Math.abs(hash % 360);
  return `hsl(${hue}, 60%, 30%)`;
};
const formatTime = ts => new Date(ts * 1000).toLocaleTimeString();

// --- Encryption Logic ---
const Encryption = {
  async getKey(password, salt) {
    const enc = new TextEncoder();
    const keyMaterial = await crypto.subtle.importKey(
      'raw', enc.encode(password), { name: 'PBKDF2' }, false, ['deriveKey']
    );
    return crypto.subtle.deriveKey(
      {
        name: 'PBKDF2',
        salt: salt,
        iterations: 100000,
        hash: 'SHA-256'
      },
      keyMaterial,
      { name: 'AES-GCM', length: 256 },
      false,
      ['encrypt', 'decrypt']
    );
  },
  async encrypt(password, message) {
    const enc = new TextEncoder();
    const salt = crypto.getRandomValues(new Uint8Array(16));
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const key = await this.getKey(password, salt);
    const encrypted = await crypto.subtle.encrypt(
      { name: 'AES-GCM', iv },
      key,
      enc.encode(message)
    );
    const data = new Uint8Array([...salt, ...iv, ...new Uint8Array(encrypted)]);
    return btoa(String.fromCharCode(...data));
  },
  async decrypt(password, encoded) {
    const data = Uint8Array.from(atob(encoded), c => c.charCodeAt(0));
    const salt = data.slice(0, 16);
    const iv = data.slice(16, 28);
    const encrypted = data.slice(28);
    const key = await this.getKey(password, salt);
    const decrypted = await crypto.subtle.decrypt(
      { name: 'AES-GCM', iv },
      key,
      encrypted
    );
    return new TextDecoder().decode(decrypted);
  }
};

// --- Settings Logic ---
const Settings = {
  get messageInterval() {
    return parseInt(getLocal('messageInterval')) || 1000;
  },
  set messageInterval(val) {
    setLocal('messageInterval', val);
  },
  get channel() {
    return getLocal('channel') || 'default';
  },
  set channel(val) {
    setLocal('channel', val);
  },
  applyToInputs() {
    intervalInput.value = this.messageInterval;
    channelInput.value = this.channel;
  },
  updateChannelName() {
    channelNameSpan.textContent = `#${this.channel || 'default'}`;
    Chat.resetAndFetchMessages();
  }
};

// --- Chat Logic ---
const Chat = {
  lastIndex: 0,
  encryptedMessages: [],
  fetchIntervalId: null,
  passwordChangeTimer: null,
  msgSendLock: false,

  async renderMessage(entry, append = true) {
    const row = document.createElement('div');
    row.className = 'message-row';
    const currentUser = nameInput.value.trim();
    const isUser = entry.name === currentUser;
    const div = document.createElement('div');
    try {
      const decrypted = await Encryption.decrypt(passwordInput.value, entry.message);
      div.className = `message ${isUser ? 'user' : 'other'}`;
      div.style.width = `${Math.min(60 + decrypted.length * 5, 500)}px`;
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
    if (append) messagesDiv.appendChild(row);
  },

  async resetAndFetchMessages() {
    this.lastIndex = 0;
    this.encryptedMessages = [];
    messagesDiv.innerHTML = '';
    this.fetchMessages();
  },

  async fetchMessages() {
    try {
      let url = `chat_read.php?from=${this.lastIndex}`;
      if (Settings.channel) url += `&channel=${encodeURIComponent(Settings.channel)}`;
      const res = await fetch(url);
      const data = await res.json();
      if (Array.isArray(data) && data.length > 0) {
        for (const entry of data) {
          this.encryptedMessages.push(entry);
          await this.renderMessage(entry);
        }
        this.lastIndex += data.length;
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
      }
    } catch (err) {
      console.error('Fetch error:', err);
    }
  },

  async retryDecryptAllMessages() {
    messagesDiv.innerHTML = '';
    for (const entry of this.encryptedMessages) {
      await this.renderMessage(entry);
    }
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  },

  restartFetchInterval() {
    if (this.fetchIntervalId) clearInterval(this.fetchIntervalId);
    this.fetchIntervalId = setInterval(() => this.fetchMessages(), Settings.messageInterval);
  },

  async sendMessage(e) {
    e.preventDefault();
    if (this.msgSendLock) return;
    this.msgSendLock = true;
    const name = nameInput.value.trim();
    const password = passwordInput.value;
    const message = messageInput.value.trim();
    if (!name || !password || !message) {
      this.msgSendLock = false;
      return;
    }
    sendButton.innerHTML = '⏳';
    setLocal('rememberedName', name);
    setLocal('rememberedPassword', password);
    const encrypted = await Encryption.encrypt(password, message);
    const formData = new FormData();
    formData.append('name', name);
    formData.append('message', encrypted);
    formData.append('channel', Settings.channel);
    try {
      const res = await fetch('chat_write.php', {
        method: 'POST',
        body: formData
      });
      const result = await res.json();
      sendButton.innerHTML = '➤';
      this.msgSendLock = false;
      if (result.success) {
        messageInput.value = '';
        this.fetchMessages();
      } else {
        alert(result.error || 'Failed to send message.');
      }
    } catch (err) {
      sendButton.innerHTML = '➤';
      this.msgSendLock = false;
      alert('Error sending message.');
      console.error(err);
    }
  }
};

// --- UI Initialization ---
(function init() {
  // Set remembered name and password
  const rememberedName = getLocal('rememberedName');
  if (rememberedName) nameInput.value = rememberedName;
  const rememberedPassword = getLocal('rememberedPassword');
  if (rememberedPassword) passwordInput.value = rememberedPassword;

  // Set settings inputs
  Settings.applyToInputs();
  Settings.updateChannelName();

  // Settings modal events
  settingsBtn.addEventListener('click', () => {
    settingsModal.style.display = 'flex';
    Settings.applyToInputs();
  });
  closeSettings.addEventListener('click', () => {
    settingsModal.style.display = 'none';
  });
  settingsForm.addEventListener('submit', e => {
    e.preventDefault();
    Settings.messageInterval = parseInt(intervalInput.value);
    Settings.channel = channelInput.value.trim() || 'default';
    settingsModal.style.display = 'none';
    Settings.updateChannelName();
    Chat.restartFetchInterval();
  });

  // Password input event
  passwordInput.addEventListener('input', () => {
    if (Chat.passwordChangeTimer) clearTimeout(Chat.passwordChangeTimer);
    Chat.passwordChangeTimer = setTimeout(() => Chat.retryDecryptAllMessages(), 3000);
  });

  // Form submit event
  form.addEventListener('submit', e => Chat.sendMessage(e));

  // Chat trigger (show/hide)
  document.addEventListener('DOMContentLoaded', function() {
    const chatTrigger = document.getElementById('chat-trigger');
    if (chatTrigger) {
      chatTrigger.addEventListener('click', function() {
        if (chatTrigger.classList.contains('open')) {
          chatTrigger.classList.remove('open');
          chatTrigger.classList.add('close');
          if (nameInput) nameInput.style.display = 'none';
          if (passwordInput) passwordInput.style.display = 'none';
        } else if (chatTrigger.classList.contains('close')) {
          chatTrigger.classList.remove('close');
          chatTrigger.classList.add('open');
          if (nameInput) nameInput.style.display = '';
          if (passwordInput) passwordInput.style.display = '';
        }
      });
    }
  });

  // Start fetching messages
  Chat.restartFetchInterval();
})();
