import { router } from 'expo-router';
import { useFocusEffect } from 'expo-router';
import { useCallback, useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';

import { EmptyState } from '../../../components/EmptyState';
import { Screen } from '../../../components/Screen';
import { colors, radius, spacing } from '../../../constants/theme';
import { ApiError } from '../../../services/api';
import { fetchOrders } from '../../../services/orders';
import type { Order } from '../../../types';

export default function OrdersScreen() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      const res = await fetchOrders();
      setOrders(res);
      setError(null);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load orders.');
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load]),
  );

  return (
    <Screen>
      {loading ? (
        <View style={styles.center}><ActivityIndicator color={colors.primary} /></View>
      ) : error ? (
        <EmptyState title="Something went wrong" message={error} />
      ) : orders.length === 0 ? (
        <EmptyState title="No orders yet" message="Your orders will appear here once you checkout." actionLabel="Start Shopping" onAction={() => router.push('/(app)/(tabs)')} />
      ) : (
        <FlatList
          data={orders}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.listContent}
          renderItem={({ item }) => (
            <Pressable style={styles.card} onPress={() => router.push(`/(app)/order/${item.id}`)}>
              <View style={styles.cardTop}>
                <Text style={styles.orderNumber}>{item.order_number}</Text>
                <View style={[styles.badge, getStatusStyle(item.order_status)]}>
                  <Text style={styles.badgeText}>{titleCase(item.order_status)}</Text>
                </View>
              </View>
              <Text style={styles.total}>₦{Number(item.total).toLocaleString()}</Text>
              <Text style={styles.meta}>{formatDate(item.created_at)}</Text>
              <Text style={styles.payment}>
                Payment: <Text style={styles.paymentValue}>{titleCase(item.payment_status)}</Text>
              </Text>
            </Pressable>
          )}
        />
      )}
    </Screen>
  );
}

function titleCase(value: string | null | undefined): string {
  if (!value) return '—';
  const readable = value.replace(/_/g, ' ').replace(/-/g, ' ');
  return readable.charAt(0).toUpperCase() + readable.slice(1);
}

function getStatusStyle(status: string) {
  if (status === 'completed' || status === 'delivered') return styles.badgeCompleted;
  if (status === 'cancelled' || status === 'failed') return styles.badgeCancelled;
  return styles.badgeDefault;
}

function formatDate(value: string | null): string {
  if (!value) return '—';
  const d = new Date(value);
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

const styles = StyleSheet.create({
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  listContent: { padding: spacing.md, paddingBottom: spacing.xl },
  card: {
    backgroundColor: colors.white,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  orderNumber: { fontSize: 16, fontWeight: '700', color: colors.text },
  badge: { paddingHorizontal: spacing.sm, paddingVertical: 4, borderRadius: radius.full },
  badgeDefault: { backgroundColor: colors.primaryLight },
  badgeCompleted: { backgroundColor: '#dcfce7' },
  badgeCancelled: { backgroundColor: '#fee2e2' },
  badgeText: { fontSize: 12, fontWeight: '600', color: colors.text },
  total: { fontSize: 18, fontWeight: '800', color: colors.primaryDark, marginTop: spacing.sm },
  meta: { color: colors.textMuted, fontSize: 13, marginTop: spacing.xs },
  payment: { color: colors.textMuted, fontSize: 13, marginTop: spacing.xs },
  paymentValue: { color: colors.text, fontWeight: '600' },
});
