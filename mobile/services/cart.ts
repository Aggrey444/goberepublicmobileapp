import { request } from './api';
import type { Cart } from '../types';

export function fetchCart(): Promise<Cart> {
  return request<Cart>({ url: '/cart', method: 'GET' });
}

export function addToCart(productId: number, quantity = 1): Promise<Cart> {
  return request<Cart>({ url: '/cart/items', method: 'POST', data: { product_id: productId, quantity } });
}

export function updateCartItem(itemId: number, quantity: number): Promise<Cart> {
  return request<Cart>({ url: `/cart/items/${itemId}`, method: 'PUT', data: { quantity } });
}

export function removeCartItem(itemId: number): Promise<Cart> {
  return request<Cart>({ url: `/cart/items/${itemId}`, method: 'DELETE' });
}

export function clearCart(): Promise<Cart> {
  return request<Cart>({ url: '/cart', method: 'DELETE' });
}
