import { useLocalSearchParams } from 'expo-router';
import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from 'react-native';

import { Button } from '../../../components/Button';
import { Screen } from '../../../components/Screen';
import { colors, radius, spacing } from '../../../constants/theme';
import { ApiError } from '../../../services/api';
import { fetchOrder } from '../../../services/orders';
import { initializePayment, verifyPayment } from '../../../services/payment';
import * as WebBrowser from 'expo-web-browser';
import type { Order } from '../../../types';

export default function OrderDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const orderId = Number(id);
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [paying, setPaying] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await fetchOrder(orderId);
      setOrder(res);
      setError(null);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load order.');
    } finally {
      setLoading(false);
    }
  }, [orderId]);

  useEffect(() => {
    load();
  }, [load]);

  const retryPayment = async () => {
    setPaying(true);
    try {
      const init = await initializePayment(orderId, 'paystack');
      if (!init.authorization_url) {
        setError('Payment is not available for this order.');
        setPaying(false);
        return;
      }
      const result = await WebBrowser.openAuthSessionAsync(init.authorization_url, 'gobe-republic://paystack');
      if (result.type === 'success') {
        await verifyPayment(init.reference);
        await load();
      } else {
        await load();
      }
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Payment could not be processed.');
    } finally {
      setPaying(false);
    }
  };

  if (loading) {
    return (
      <Screen style={styles.center}>
        <ActivityIndicator color={colors.primary} />
      </Screen>
    );
  }

  if (error || !order) {
    return (
      <Screen style={styles.center}>
        <Text style={styles.error}>{error ?? 'Order not found.'}</Text>
      </Screen>
    );
  }

  const canPay = order.payment_status === 'pending' || order.payment_status === 'failed';

  return (
    <Screen>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={styles.orderNumber}>{order.order_number}</Text>
        <View style={styles.statusRow}>
          <View style={[styles.badge, { backgroundColor: order.order_status === 'cancelled' ? '#fee2e2' : '#dcfce7' }]}>
            <Text style={styles.badgeText}>Order: {titleCase(order.order_status)}</Text>
          </View>
          <View style={[styles.badge, { backgroundColor: order.payment_status === 'paid' ? '#dcfce7' : '#fef3c7' }]}>
            <Text style={styles.badgeText}>Payment: {titleCase(order.payment_status)}</Text>
          </View>
        </View>

        <Text style={styles.sectionTitle}>Items</Text>
        <View style={styles.card}>
          {(order.items ?? []).map((item) => (
            <View key={item.id} style={styles.itemRow}>
              <View style={styles.itemInfo}>
                <Text style={styles.itemName}>{item.product_name}</Text>
                <Text style={styles.itemMeta}>
                  {item.quantity} × ₦{Number(item.unit_price).toLocaleString()}
                </Text>
              </View>
              <Text style={styles.itemTotal}>₦{Number(item.total).toLocaleString()}</Text>
            </View>
          ))}
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>₦{Number(order.subtotal).toLocaleString()}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Delivery</Text>
            <Text style={styles.summaryValue}>₦{Number(order.delivery_fee).toLocaleString()}</Text>
          </View>
          <View style={[styles.totalRow, styles.summaryRow]}>
            <Text style={styles.totalLabel}>Total</Text>
            <Text style={styles.totalValue}>₦{Number(order.total).toLocaleString()}</Text>
          </View>
        </View>

        {order.delivery_information ? (
          <>
            <Text style={styles.sectionTitle}>Delivery</Text>
            <View style={styles.card}>
              <Text style={styles.deliveryName}>{order.delivery_information.recipient_name}</Text>
              <Text style={styles.deliveryMeta}>{order.delivery_information.phone}</Text>
              <Text style={styles.deliveryMeta}>{order.delivery_information.address}</Text>
              {order.delivery_information.city ? (
                <Text style={styles.deliveryMeta}>{order.delivery_information.city}</Text>
              ) : null}
              {order.delivery_information.additional_notes ? (
                <Text style={styles.deliveryNotes}>“{order.delivery_information.additional_notes}”</Text>
              ) : null}
            </View>
          </>
        ) : null}

        <Text style={styles.meta}>Placed: {formatDate(order.created_at)}</Text>

        {canPay ? (
          <Button title="Pay Now" onPress={retryPayment} loading={paying} style={styles.payBtn} />
        ) : null}
      </ScrollView>
    </Screen>
  );
}

function titleCase(value: string | null | undefined): string {
  if (!value) return '—';
  const readable = value.replace(/_/g, ' ').replace(/-/g, ' ');
  return readable.charAt(0).toUpperCase() + readable.slice(1);
}

function formatDate(value: string | null): string {
  if (!value) return '—';
  const d = new Date(value);
  return d.toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

const styles = StyleSheet.create({
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: spacing.lg },
  error: { color: colors.danger, fontSize: 16 },
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  orderNumber: { fontSize: 22, fontWeight: '800', color: colors.text },
  statusRow: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm, marginBottom: spacing.lg, flexWrap: 'wrap' },
  badge: { paddingHorizontal: spacing.sm, paddingVertical: 4, borderRadius: radius.full },
  badgeText: { fontSize: 12, fontWeight: '700', color: colors.text },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: colors.text, marginTop: spacing.md, marginBottom: spacing.sm },
  card: { backgroundColor: colors.surface, borderRadius: radius.md, padding: spacing.md },
  itemRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.sm },
  itemInfo: { flex: 1, marginRight: spacing.md },
  itemName: { color: colors.text, fontWeight: '600', fontSize: 15 },
  itemMeta: { color: colors.textMuted, fontSize: 13, marginTop: 2 },
  itemTotal: { color: colors.text, fontWeight: '600' },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: spacing.sm },
  summaryLabel: { color: colors.textMuted, fontSize: 14 },
  summaryValue: { color: colors.text, fontWeight: '600', fontSize: 14 },
  totalRow: { borderTopWidth: 1, borderTopColor: colors.border, paddingTop: spacing.sm },
  totalLabel: { color: colors.text, fontWeight: '700', fontSize: 15 },
  totalValue: { color: colors.primaryDark, fontWeight: '800', fontSize: 15 },
  deliveryName: { color: colors.text, fontWeight: '700', fontSize: 15 },
  deliveryMeta: { color: colors.textMuted, fontSize: 14, marginTop: spacing.xs },
  deliveryNotes: { color: colors.text, fontStyle: 'italic', fontSize: 14, marginTop: spacing.sm },
  meta: { color: colors.textMuted, fontSize: 13, marginTop: spacing.lg },
  payBtn: { marginTop: spacing.lg },
});
