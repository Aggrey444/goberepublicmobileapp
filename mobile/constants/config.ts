import { Platform } from 'react-native';

function resolveDefaultApiUrl(): string {
  // Override with EXPO_PUBLIC_API_URL (set at build time for production).
  const envUrl = process.env.EXPO_PUBLIC_API_URL;
  if (envUrl) {
    return envUrl.replace(/\/$/, '');
  }

  // Development defaults.
  // Android emulator maps host loopback to 10.0.2.2.
  if (Platform.OS === 'android') {
    return 'http://10.0.2.2:8000';
  }

  return 'http://127.0.0.1:8000';
}

export const API_URL = resolveDefaultApiUrl();
export const API_BASE = `${API_URL}/api/v1`;
export const STORAGE_URL = `${API_URL}/storage`;
