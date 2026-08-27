import { request } from './api';
import type { Category, Paginated, Product } from '../types';

export function fetchCategories(): Promise<Category[]> {
  return request<Category[]>({ url: '/categories', method: 'GET' });
}

export interface ProductQuery {
  page?: number;
  per_page?: number;
  search?: string;
  category_id?: number;
}

export function fetchProducts(params: ProductQuery): Promise<Paginated<Product>> {
  return request<Paginated<Product>>({
    url: '/products',
    method: 'GET',
    params: { per_page: 20, ...params },
  });
}

export function fetchProduct(id: number): Promise<Product> {
  return request<Product>({ url: `/products/${id}`, method: 'GET' });
}
