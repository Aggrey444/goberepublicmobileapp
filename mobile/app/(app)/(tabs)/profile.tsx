import { router } from 'expo-router';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';

import { Button } from '../../../components/Button';
import { Screen } from '../../../components/Screen';
import { colors, radius, spacing } from '../../../constants/theme';
import { useAuthStore } from '../../../stores/authStore';
import { useCartStore } from '../../../stores/cartStore';

export default function ProfileScreen() {
  const user = useAuthStore((s) => s.user);
  const signOut = useAuthStore((s) => s.signOut);
  const resetCart = useCartStore((s) => s.reset);

  const handleLogout = async () => {
    Alert.alert('Sign Out', 'Are you sure you want to sign out?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Sign Out',
        style: 'destructive',
        onPress: async () => {
          await signOut();
          resetCart();
          router.replace('/(app)/login');
        },
      },
    ]);
  };

  return (
    <Screen>
      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>{(user?.name ?? 'G').charAt(0).toUpperCase()}</Text>
        </View>
        <Text style={styles.name}>{user?.name ?? 'User'}</Text>
        <Text style={styles.email}>{user?.email ?? '—'}</Text>
        {user?.phone ? <Text style={styles.phone}>{user.phone}</Text> : null}
        <Text style={styles.role}>Role: {user?.role ?? 'customer'}</Text>

        <Button
          title="Update Profile"
          variant="outline"
          style={styles.action}
          onPress={() => Alert.alert('Coming Soon', 'Profile editing is not yet available.')}
        />
        <Button title="Sign Out" variant="danger" style={styles.action} onPress={handleLogout} />
      </ScrollView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, alignItems: 'center' },
  avatar: {
    width: 88,
    height: 88,
    borderRadius: radius.full,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: spacing.md,
  },
  avatarText: { fontSize: 36, fontWeight: '800', color: colors.white },
  name: { fontSize: 22, fontWeight: '700', color: colors.text, marginTop: spacing.md },
  email: { color: colors.textMuted, marginTop: spacing.xs },
  phone: { color: colors.textMuted, marginTop: spacing.xs },
  role: { color: colors.textMuted, marginTop: spacing.xs, textTransform: 'capitalize' },
  action: { alignSelf: 'stretch', marginTop: spacing.lg },
});
