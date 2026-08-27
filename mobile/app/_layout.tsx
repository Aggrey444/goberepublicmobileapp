import { Stack } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { useEffect } from 'react';

import { useAuthStore } from '../stores/authStore';
import { registerPushToken } from '../services/pushNotifications';

export default function RootLayout() {
  const user = useAuthStore((s) => s.user);
  const isHydrated = useAuthStore((s) => s.isHydrated);

  useEffect(() => {
    if (user) {
      registerPushToken().catch(() => {});
    }
  }, [user]);

  const initialRoute = !isHydrated || !user ? '(app)/index' : '(app)/(tabs)';

  return (
    <>
      <StatusBar style="dark" />
      <Stack screenOptions={{ headerShown: false }} initialRouteName={initialRoute} />
    </>
  );
}
