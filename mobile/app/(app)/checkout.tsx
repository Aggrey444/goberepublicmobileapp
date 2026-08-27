import { router } from 'expo-router';
import * as WebBrowser from 'expo-web-browser';
import { useMemo, useState } from 'react';
import { ActivityIndicator, Alert, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, View } from 'react-native';

import { Button } from '../../components/Button';
import { Input } from '../../components/Input';
import { Screen } from '../../components/Screen';
import { colors, radius, spacing } from '../../constants/theme';
import { ApiError } from '../../services/api';
import { fetchCart } from '../../services/cart';
import { checkout } from '../../services/orders';
import { initializePayment, verifyPayment } from '../../services/payment';
import { useCartStore } from '../../stores/cartStore';
import type { Cart } from '../../types';

export default function CheckoutScreen() {
  const setCart = useCartStore((s) => s.setCart);
  const [cart, setLocalCart] = useState<Cart | null>(null);
  const [recipientName, setRecipientName] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [city, setCity] = useState('');
  const [notes, setNotes] = useState('');
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const subtotal = cart?.subtotal ?? 0;
  const deliveryFee = 0; // TBD: delivery fee calculation not defined yet
  const total = subtotal + deliveryFee;

  useMemo(() => {
    let active = true;
    (async () => {
      try {
        const res = await fetchCart();
        if (active) {
          setLocalCart(res);
          setCart(res);
        }
      } catch (err) {
        if (active) setError(err instanceof ApiError ? err.message : 'Failed to load cart.');
      } finally {
        if (active) setLoading(false);
      }
    })();
    return () => {
      active = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const placeOrder = async () => {
    if (!recipientName || !phone || !address) {
      setError('Please fill in recipient name, phone and address.');
      return;
    }
    setError(null);
    setSubmitting(true);
    try {
      const order = await checkout({
        delivery_information: {
          recipient_name: recipientName,
          phone,
          address,
          city: city || undefined,
          additional_notes: notes || undefined,
        },
      });
      await handlePayment(order.id);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to place order.');
      setSubmitting(false);
    }
  };

  const handlePayment = async (orderId: number) => {
    try {
      const init = await initializePayment(orderId, 'paystack');

      if (!init.authorization_url) {
        // No Paystack configured; order placed as unpaid.
        Alert.alert('Order Placed', 'Your order was placed successfully.');
        setSubmitting(false);
        router.replace(`/(app)/order/${orderId}`);
        return;
      }

      const result = await WebBrowser.openAuthSessionAsync(init.authorization_url, 'gobe-republic://paystack');
      if (result.type === 'success') {
        await verifyPayment(init.reference);
        Alert.alert('Payment Received', 'Thank you! Your payment was successful.');
        setSubmitting(false);
        router.replace(`/(app)/order/${orderId}`);
      } else {
        Alert.alert('Payment Incomplete', 'Your order is placed but the payment was not completed.');
        setSubmitting(false);
        router.replace(`/(app)/order/${orderId}`);
      }
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Payment could not be processed.');
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <Screen style={styles.center}>
        <ActivityIndicator color={colors.primary} />
      </Screen>
    );
  }

  return (
    <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
        <Text style={styles.sectionTitle}>Delivery Information</Text>
        <Input label="Recipient Name" value={recipientName} onChangeText={setRecipientName} placeholder="Full name" />
        <Input label="Phone" value={phone} onChangeText={setPhone} keyboardType="phone-pad" placeholder="+234..." />
        <Input label="Address" value={address} onChangeText={setAddress} placeholder="Street, area" />
        <Input label="City" value={city} onChangeText={setCity} placeholder="City" />
        <Input label="Additional Notes (optional)" value={notes} onChangeText={setNotes} multiline />

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <View style={styles.summary}>
          <Text style={styles.sectionTitle}>Order Summary</Text>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>₦{subtotal.toLocaleString()}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Delivery</Text>
            <Text style={styles.summaryValue}>₦{deliveryFee.toLocaleString()}</Text>
          </View>
          <View style={[styles.summaryRow, styles.totalRow]}>
            <Text style={styles.totalLabel}>Total</Text>
            <Text style={styles.totalValue}>₦{total.toLocaleString()}</Text>
          </View>
        </View>

        <Button title="Place Order & Pay" onPress={placeOrder} loading={submitting} />
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  sectionTitle: { fontSize: 18, fontWeight: '700', color: colors.text, marginBottom: spacing.md },
  error: { color: colors.danger, marginBottom: spacing.md },
  summary: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.md,
    marginVertical: spacing.lg,
  },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.sm },
  totalRow: { borderTopWidth: 1, borderTopColor: colors.border, paddingTop: spacing.sm, marginTop: spacing.sm },
  summaryLabel: { color: colors.textMuted, fontSize: 15 },
  summaryValue: { color: colors.text, fontWeight: '600', fontSize: 15 },
  totalLabel: { color: colors.text, fontSize: 16, fontWeight: '700' },
  totalValue: { color: colors.primaryDark, fontSize: 16, fontWeight: '800' },
});
