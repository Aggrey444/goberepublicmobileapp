import { request } from './api';

export function registerDeviceToken(token: string): Promise<void> {
  return request<void>({ url: '/device-tokens', method: 'POST', data: { token } });
}

export function unregisterDeviceToken(token: string): Promise<void> {
  return request<void>({ url: '/device-tokens', method: 'DELETE', data: { token } });
}
