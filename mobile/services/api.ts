import axios, { AxiosError, type AxiosRequestConfig } from 'axios';

import { API_BASE } from '../constants/config';
import { getToken } from '../stores/tokenStore';
import type { ApiResponse } from '../types';

const api = axios.create({
  baseURL: API_BASE,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use(async (config) => {
  const token = await getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export class ApiError extends Error {
  status: number;
  errors?: Record<string, string[]>;

  constructor(message: string, status: number, errors?: Record<string, string[]>) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

export async function request<T>(config: AxiosRequestConfig): Promise<T> {
  try {
    const res = await api.request<ApiResponse<T>>(config);
    return res.data.data as T;
  } catch (err) {
    const error = err as AxiosError<ApiResponse>;
    const status = error.response?.status ?? 0;
    const message =
      error.response?.data?.message ?? error.message ?? 'Something went wrong. Please try again.';
    throw new ApiError(message, status, error.response?.data?.errors);
  }
}

export default api;
