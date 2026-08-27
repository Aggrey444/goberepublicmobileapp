import { request } from './api';
import type { AppNotification } from '../types';

export function fetchNotifications(): Promise<AppNotification[]> {
  return request<AppNotification[]>({ url: '/notifications', method: 'GET' });
}

export function markNotificationRead(id: number): Promise<void> {
  return request<void>({ url: `/notifications/${id}/read`, method: 'POST' });
}

export function markAllNotificationsRead(): Promise<void> {
  return request<void>({ url: '/notifications/read-all', method: 'POST' });
}
