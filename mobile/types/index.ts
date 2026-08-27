export interface ApiResponse<T = unknown> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface Paginated<T> {
  items: T[];
  pagination: Pagination;
}

export interface User {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  role: string;
  status: string;
}

export interface AuthPayload {
  user: User;
  token: string;
}

export interface Category {
  id: number;
  name: string;
  description: string | null;
  image: string | null;
  image_url: string | null;
  status: string;
}

export interface Product {
  id: number;
  category_id: number | null;
  name: string;
  description: string | null;
  price: number;
  image: string | null;
  image_url: string | null;
  status: string;
  category?: Category | null;
}

export interface CartItem {
  id: number;
  product_id: number;
  quantity: number;
  unit_price: number;
  line_total: number;
  product?: Product | null;
}

export interface Cart {
  id: number;
  user_id: number;
  items: CartItem[];
  subtotal: number;
  item_count: number;
}

export interface OrderItem {
  id: number;
  product_id: number | null;
  product_name: string;
  quantity: number;
  unit_price: number;
  total: number;
}

export interface DeliveryInformation {
  id: number;
  recipient_name: string;
  phone: string;
  address: string;
  city: string | null;
  additional_notes: string | null;
}

export interface Payment {
  id: number;
  provider: string;
  reference: string;
  access_code: string | null;
  authorization_url: string | null;
  amount: number;
  currency: string;
  status: string;
  paid_at: string | null;
}

export interface Order {
  id: number;
  order_number: string;
  subtotal: number;
  delivery_fee: number;
  total: number;
  payment_status: string;
  order_status: string;
  created_at: string | null;
  items?: OrderItem[];
  delivery_information?: DeliveryInformation | null;
  payment?: Payment[];
}

export interface InitializePaymentPayload {
  payment: Payment;
  authorization_url: string | null;
  access_code: string | null;
  reference: string;
}

export interface AppNotification {
  id: number;
  type: string;
  title: string;
  body: string;
  data: unknown;
  read_at: string | null;
  created_at: string;
}
