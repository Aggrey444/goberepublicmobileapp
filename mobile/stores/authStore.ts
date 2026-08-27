import AsyncStorage from '@react-native-async-storage/async-storage';
import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

import type { User } from '../types';
import { clearToken, setToken } from './tokenStore';

interface AuthState {
  user: User | null;
  isHydrated: boolean;
  setHydrated: (v: boolean) => void;
  signIn: (token: string, user: User) => Promise<void>;
  setUser: (user: User) => void;
  signOut: () => Promise<void>;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      isHydrated: false,
      setHydrated: (v) => set({ isHydrated: v }),
      signIn: async (token, user) => {
        await setToken(token);
        set({ user });
      },
      setUser: (user) => set({ user }),
      signOut: async () => {
        await clearToken();
        set({ user: null });
      },
    }),
    {
      name: 'gobe_auth',
      storage: createJSONStorage(() => AsyncStorage),
      partialize: (state) => ({ user: state.user }),
      onRehydrateStorage: () => (state) => {
        state?.setHydrated(true);
      },
    },
  ),
);
