import { request } from './api';
import type { AuthPayload, User } from '../types';

export interface LoginData {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  phone?: string;
  password: string;
  password_confirmation?: string;
}

export function login(data: LoginData): Promise<AuthPayload> {
  return request<AuthPayload>({ url: '/auth/login', method: 'POST', data });
}

export function register(data: RegisterData): Promise<AuthPayload> {
  return request<AuthPayload>({ url: '/auth/register', method: 'POST', data });
}

export function logout(): Promise<void> {
  return request<void>({ url: '/auth/logout', method: 'POST' });
}

export function fetchProfile(): Promise<User> {
  return request<User>({ url: '/auth/me', method: 'GET' });
}
