import { request } from './api';
import type { InitializePaymentPayload, Order } from '../types';

export interface VerifyResult {
  success: boolean;
  message: string;
  order?: Order;
}

export function initializePayment(orderId: number, method: 'paystack' | 'bank'): Promise<InitializePaymentPayload> {
  return request<InitializePaymentPayload>({
    url: '/payments/initialize',
    method: 'POST',
    data: { order_id: orderId, method },
  });
}

export function verifyPayment(reference: string): Promise<VerifyResult> {
  return request<VerifyResult>({
    url: '/payments/verify',
    method: 'POST',
    data: { reference },
  });
}
