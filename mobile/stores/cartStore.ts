import { create } from 'zustand';

import type { Cart } from '../types';

interface CartState {
  cart: Cart | null;
  loading: boolean;
  setCart: (cart: Cart | null) => void;
  setLoading: (loading: boolean) => void;
  reset: () => void;
}

export const useCartStore = create<CartState>((set) => ({
  cart: null,
  loading: false,
  setCart: (cart) => set({ cart }),
  setLoading: (loading) => set({ loading }),
  reset: () => set({ cart: null, loading: false }),
}));
