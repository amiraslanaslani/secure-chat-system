export class VaultService {
    static channelPasswords: Map<string, string> = new Map([]);
    
    static async getPassword(channel: string): Promise<string | undefined> {
      return this.channelPasswords.get(channel);
    }

    static async setPassword(channel: string, password: string) {
        this.channelPasswords.set(channel, password);
    }
}
