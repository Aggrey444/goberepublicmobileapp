import { Redirect, Stack } from 'expo-router';

import { useAuthStore } from '../../stores/authStore';

export default function AppLayout() {
  const user = useAuthStore((s) => s.user);
  const isHydrated = useAuthStore((s) => s.isHydrated);

  if (!isHydrated) {
    return null;
  }

  if (!user) {
    return <Redirect href="/(app)/login" />;
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="(tabs)" />
      <Stack.Screen name="product/[id]" options={{ headerShown: true, title: 'Product' }} />
      <Stack.Screen name="checkout" options={{ headerShown: true, title: 'Checkout' }} />
      <Stack.Screen name="order/[id]" options={{ headerShown: true, title: 'Order' }} />
    </Stack>
  );
}
