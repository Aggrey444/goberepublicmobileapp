import { router } from 'expo-router';
import { useFocusEffect } from 'expo-router';
import { useCallback, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, Text, View } from 'react-native';

import { Button } from '../../../components/Button';
import { EmptyState } from '../../../components/EmptyState';
import { Screen } from '../../../components/Screen';
import { colors, radius, spacing } from '../../../constants/theme';
import { ApiError } from '../../../services/api';
import { fetchCart, removeCartItem, updateCartItem } from '../../../services/cart';
import { useAuthStore } from '../../../stores/authStore';
import { useCartStore } from '../../../stores/cartStore';
import type { CartItem } from '../../../types';

export default function CartScreen() {
  const user = useAuthStore((s) => s.user);
  const cart = useCartStore((s) => s.cart);
  const setCart = useCartStore((s) => s.setCart);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const refresh = useCallback(async () => {
    if (!user) return;
    try {
      if (cart === null) setLoading(true);
      const res = await fetchCart();
      setCart(res);
      setError(null);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load cart.');
    } finally {
      setLoading(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user, cart === null]);

  useFocusEffect(
    useCallback(() => {
      refresh();
    }, [refresh]),
  );

  const changeQty = async (item: CartItem, delta: number) => {
    const next = item.quantity + delta;
    if (next < 1) return;
    try {
      const res = await updateCartItem(item.id, next);
      setCart(res);
    } catch {
      // keep UI unchanged on error
    }
  };

  const remove = async (item: CartItem) => {
    try {
      const res = await removeCartItem(item.id);
      setCart(res);
    } catch {
      // ignore
    }
  };

  if (loading || !user) {
    return (
      <Screen style={styles.center}>
        <ActivityIndicator color={colors.primary} />
      </Screen>
    );
  }

  const itemCount = cart?.item_count ?? 0;

  return (
    <Screen>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      {itemCount === 0 ? (
        <EmptyState
          title="Your cart is empty"
          message="Browse our products and add something you love."
          actionLabel="Browse Products"
          onAction={() => router.push('/(app)/(tabs)')}
        />
      ) : (
        <FlatList
          data={cart?.items ?? []}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.listContent}
          renderItem={({ item }) => (
            <View style={styles.item}>
              <View style={styles.itemInfo}>
                <Text style={styles.itemName}>{item.product?.name ?? `Product #${item.product_id}`}</Text>
                <Text style={styles.itemPrice}>
                  ₦{Number(item.unit_price).toLocaleString()} × {item.quantity}
                </Text>
                <Text style={styles.lineTotal}>₦{Number(item.line_total).toLocaleString()}</Text>
              </View>
              <View style={styles.qtyRow}>
                <Button title="−" variant="outline" style={styles.qtyBtn} onPress={() => changeQty(item, -1)} />
                <Text style={styles.qty}>{item.quantity}</Text>
                <Button title="+" variant="outline" style={styles.qtyBtn} onPress={() => changeQty(item, 1)} />
              </View>
              <Button title="Remove" variant="ghost" style={styles.removeBtn} onPress={() => remove(item)} />
            </View>
          )}
          ListFooterComponent={
            <View style={styles.footer}>
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Subtotal</Text>
                <Text style={styles.summaryValue}>₦{Number(cart?.subtotal ?? 0).toLocaleString()}</Text>
              </View>
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Delivery</Text>
                <Text style={styles.summaryValue}>Calculated at checkout</Text>
              </View>
              <Button title="Proceed to Checkout" onPress={() => router.push('/(app)/checkout')} loading={false} />
            </View>
          }
        />
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  center: { alignItems: 'center', justifyContent: 'center' },
  error: { color: colors.danger, textAlign: 'center', padding: spacing.md },
  listContent: { padding: spacing.md, paddingBottom: spacing.xl },
  item: {
    backgroundColor: colors.white,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  itemInfo: { marginBottom: spacing.sm },
  itemName: { fontSize: 16, fontWeight: '600', color: colors.text },
  itemPrice: { color: colors.textMuted, fontSize: 13, marginTop: spacing.xs },
  lineTotal: { color: colors.primaryDark, fontWeight: '700', fontSize: 15, marginTop: spacing.xs },
  qtyRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, marginTop: spacing.sm },
  qtyBtn: { height: 40, width: 44, paddingHorizontal: 0 },
  qty: { fontSize: 16, fontWeight: '700', minWidth: 24, textAlign: 'center' },
  removeBtn: { marginTop: spacing.sm, alignSelf: 'flex-start' },
  footer: { marginTop: spacing.sm },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.sm },
  summaryLabel: { color: colors.textMuted, fontSize: 15 },
  summaryValue: { color: colors.text, fontWeight: '600', fontSize: 15 },
});
