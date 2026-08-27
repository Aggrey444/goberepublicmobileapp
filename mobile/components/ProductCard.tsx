import { Image, Pressable, StyleSheet, Text, View } from 'react-native';

import { colors, radius, spacing } from '../constants/theme';
import { STORAGE_URL } from '../constants/config';
import type { Product } from '../types';

interface ProductCardProps {
  product: Product;
  onPress: (product: Product) => void;
}

export function ProductCard({ product, onPress }: ProductCardProps) {
  const imageUrl = product.image_url ?? (product.image ? `${STORAGE_URL}/${product.image}` : null);

  return (
    <Pressable style={[styles.card, { opacity: product.status === 'active' ? 1 : 0.5 }]} onPress={() => onPress(product)}>
      <View style={styles.imageWrap}>
        {imageUrl ? (
          <Image source={{ uri: imageUrl }} style={styles.image} resizeMode="cover" />
        ) : (
          <View style={[styles.image, styles.placeholder]}>
            <Text style={styles.placeholderText}>{product.name.charAt(0).toUpperCase()}</Text>
          </View>
        )}
      </View>
      <View style={styles.info}>
        <Text style={styles.name} numberOfLines={2}>
          {product.name}
        </Text>
        <Text style={styles.price}>₦{Number(product.price).toLocaleString()}</Text>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    flex: 1,
    backgroundColor: colors.white,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: colors.border,
    overflow: 'hidden',
    marginBottom: spacing.md,
  },
  imageWrap: {
    aspectRatio: 1,
    backgroundColor: colors.surface,
  },
  image: {
    width: '100%',
    height: '100%',
  },
  placeholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  placeholderText: {
    fontSize: 40,
    fontWeight: '700',
    color: colors.primary,
  },
  info: {
    padding: spacing.sm,
  },
  name: {
    fontSize: 14,
    color: colors.text,
    fontWeight: '500',
  },
  price: {
    fontSize: 15,
    fontWeight: '700',
    color: colors.primaryDark,
    marginTop: spacing.xs,
  },
});
