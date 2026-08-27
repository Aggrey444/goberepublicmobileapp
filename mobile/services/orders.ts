import { request } from './api';
import type { Order } from '../types';

export interface DeliveryInfoInput {
  recipient_name: string;
  phone: string;
  address: string;
  city?: string;
  additional_notes?: string;
}

export interface CheckoutPayload {
  delivery_information: DeliveryInfoInput;
}

export function fetchOrders(): Promise<Order[]> {
  return request<Order[]>({ url: '/orders', method: 'GET' });
}

export function fetchOrder(id: number): Promise<Order> {
  return request<Order>({ url: `/orders/${id}`, method: 'GET' });
}

export function checkout(payload: CheckoutPayload): Promise<Order> {
  return request<Order>({ url: '/orders', method: 'POST', data: payload });
}
