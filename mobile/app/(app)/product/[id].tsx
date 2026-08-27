import { Ionicons } from '@expo/vector-icons';
import { router, useLocalSearchParams } from 'expo-router';
import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, Image, ScrollView, StyleSheet, Text, View } from 'react-native';

import { Button } from '../../../components/Button';
import { Screen } from '../../../components/Screen';
import { STORAGE_URL } from '../../../constants/config';
import { colors, radius, spacing } from '../../../constants/theme';
import { ApiError } from '../../../services/api';
import { addToCart } from '../../../services/cart';
import { fetchProduct } from '../../../services/products';
import { useAuthStore } from '../../../stores/authStore';
import { useCartStore } from '../../../stores/cartStore';
import type { Product } from '../../../types';

export default function ProductDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const productId = Number(id);
  const user = useAuthStore((s) => s.user);
  const setCart = useCartStore((s) => s.setCart);

  const [product, setProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [adding, setAdding] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await fetchProduct(productId);
      setProduct(res);
      setError(null);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load product.');
    } finally {
      setLoading(false);
    }
  }, [productId]);

  useEffect(() => {
    load();
  }, [load]);

  const handleAdd = async () => {
    if (!user) {
      router.push('/(app)/login');
      return;
    }
    setAdding(true);
    try {
      const cart = await addToCart(productId);
      setCart(cart);
      router.push('/(app)/(tabs)');
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to add to cart.');
    } finally {
      setAdding(false);
    }
  };

  if (loading) {
    return (
      <Screen style={styles.center}>
        <ActivityIndicator color={colors.primary} />
      </Screen>
    );
  }

  if (error || !product) {
    return (
      <Screen style={styles.center}>
        <Text style={styles.error}>{error ?? 'Product not found.'}</Text>
        <Button title="Back to Home" variant="outline" onPress={() => router.back()} />
      </Screen>
    );
  }

  const imageUrl = product.image_url ?? (product.image ? `${STORAGE_URL}/${product.image}` : null);

  return (
    <Screen>
      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.imageWrap}>
          {imageUrl ? (
            <Image source={{ uri: imageUrl }} style={styles.image} resizeMode="cover" />
          ) : (
            <View style={[styles.image, styles.placeholder]}>
              <Ionicons name="cube-outline" color={colors.primary} size={80} />
            </View>
          )}
        </View>

        <View style={styles.info}>
          <Text style={styles.name}>{product.name}</Text>
          <Text style={styles.price}>₦{Number(product.price).toLocaleString()}</Text>
          <Text style={styles.category}>{product.category?.name ?? ''}</Text>
          <Text style={styles.description}>{product.description ?? 'No description available.'}</Text>
        </View>
      </ScrollView>

      <View style={styles.footer}>
        <Button
          title={user ? 'Add to Cart' : 'Sign In to Buy'}
          onPress={handleAdd}
          loading={adding}
        />
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: spacing.lg },
  error: { color: colors.danger, fontSize: 16, marginBottom: spacing.lg },
  content: { paddingBottom: spacing.xl },
  imageWrap: { width: '100%', aspectRatio: 1, backgroundColor: colors.surface },
  image: { width: '100%', height: '100%' },
  placeholder: { alignItems: 'center', justifyContent: 'center' },
  info: { padding: spacing.lg },
  name: { fontSize: 24, fontWeight: '700', color: colors.text },
  price: { fontSize: 22, fontWeight: '800', color: colors.primaryDark, marginTop: spacing.sm },
  category: { color: colors.textMuted, marginTop: spacing.xs, textTransform: 'capitalize' },
  description: { color: colors.text, fontSize: 15, lineHeight: 22, marginTop: spacing.lg },
  footer: {
    padding: spacing.md,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    backgroundColor: colors.white,
  },
});
