import { Redirect } from 'expo-router';

import { useAuthStore } from '../stores/authStore';

export default function Index() {
  const user = useAuthStore((s) => s.user);
  const isHydrated = useAuthStore((s) => s.isHydrated);

  // Redirect based on auth state. The (app) group layout also guards.
  if (!isHydrated) return null;
  if (!user) return <Redirect href="/(app)/login" />;
  return <Redirect href="/(app)/(tabs)" />;
}
