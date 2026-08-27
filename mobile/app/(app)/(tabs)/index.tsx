import { router } from 'expo-router';
import { useCallback, useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import { ProductCard } from '../../../components/ProductCard';
import { Screen } from '../../../components/Screen';
import { colors, radius, spacing } from '../../../constants/theme';
import { ApiError } from '../../../services/api';
import { fetchCategories, fetchProducts } from '../../../services/products';
import type { Category, Product } from '../../../types';

export default function HomeScreen() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<number | null>(null);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const loadProducts = useCallback(async (reset = false, cat: number | null = selectedCategory, q = search) => {
    try {
      if (reset) setLoading(true);
      const targetPage = reset ? 1 : page;
      const res = await fetchProducts({
        page: targetPage,
        category_id: cat ?? undefined,
        search: q || undefined,
      });
      setProducts((prev) => (reset ? res.items : [...prev, ...res.items]));
      setPage(res.pagination.current_page + 1);
      setHasMore(res.pagination.current_page < res.pagination.last_page);
      setError(null);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Failed to load products.');
    } finally {
      setLoading(false);
      setRefreshing(false);
      setLoadingMore(false);
    }
  }, [page, search, selectedCategory]);

  const loadCategories = useCallback(async () => {
    try {
      const res = await fetchCategories();
      setCategories(res);
    } catch {
      // non-fatal
    }
  }, []);

  const refresh = useCallback(() => {
    setRefreshing(true);
    setPage(1);
    loadCategories();
    loadProducts(true);
  }, [loadProducts, loadCategories]);

  useEffect(() => {
    loadCategories();
    loadProducts(true);
  }, [loadProducts, loadCategories]);

  useEffect(() => {
    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      setPage(1);
      loadProducts(true);
    }, 400);
    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search, selectedCategory]);

  const onEndReached = () => {
    if (hasMore && !loadingMore && !loading) {
      setLoadingMore(true);
      loadProducts(false);
    }
  };

  return (
    <Screen>
      <View style={styles.header}>
        <Text style={styles.brand}>GOBE Republic</Text>
        <TextInput
          style={styles.search}
          placeholder="Search products..."
          value={search}
          onChangeText={setSearch}
          placeholderTextColor={colors.textMuted}
        />
      </View>

      <FlatList
        data={products}
        keyExtractor={(item) => String(item.id)}
        numColumns={2}
        columnWrapperStyle={styles.row}
        contentContainerStyle={styles.listContent}
        keyboardShouldPersistTaps="handled"
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={refresh} />}
        onEndReached={onEndReached}
        onEndReachedThreshold={0.3}
        ListHeaderComponent={
          <>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chips}>
              <Pressable
                style={[styles.chip, selectedCategory === null && styles.chipActive]}
                onPress={() => setSelectedCategory(null)}
              >
                <Text style={[styles.chipText, selectedCategory === null && styles.chipTextActive]}>All</Text>
              </Pressable>
              {categories.map((c) => (
                <Pressable
                  key={c.id}
                  style={[styles.chip, selectedCategory === c.id && styles.chipActive]}
                  onPress={() => setSelectedCategory(c.id)}
                >
                  <Text style={[styles.chipText, selectedCategory === c.id && styles.chipTextActive]}>{c.name}</Text>
                </Pressable>
              ))}
            </ScrollView>
            {error ? <Text style={styles.error}>{error}</Text> : null}
          </>
        }
        ListEmptyComponent={
          loading ? (
            <View style={styles.center}><ActivityIndicator color={colors.primary} /></View>
          ) : (
            <View style={styles.center}><Text style={styles.empty}>No products found.</Text></View>
          )
        }
        ListFooterComponent={
          loadingMore ? <ActivityIndicator color={colors.primary} style={styles.footerLoader} /> : null
        }
        renderItem={({ item }) => (
          <ProductCard product={item} onPress={(p) => router.push(`/(app)/product/${p.id}`)} />
        )}
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  header: { padding: spacing.md, paddingBottom: spacing.sm },
  brand: { fontSize: 24, fontWeight: '800', color: colors.primaryDark, marginBottom: spacing.md },
  search: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    height: 44,
    fontSize: 15,
    backgroundColor: colors.white,
    color: colors.text,
  },
  listContent: { paddingHorizontal: spacing.md, paddingBottom: spacing.xl },
  row: { justifyContent: 'space-between', gap: spacing.sm },
  chips: { paddingVertical: spacing.sm, gap: spacing.sm },
  chip: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.full,
    borderWidth: 1,
    borderColor: colors.border,
    backgroundColor: colors.white,
  },
  chipActive: { backgroundColor: colors.primary, borderColor: colors.primary },
  chipText: { color: colors.text, fontWeight: '600', fontSize: 14 },
  chipTextActive: { color: colors.white },
  error: { color: colors.danger, textAlign: 'center', marginTop: spacing.sm },
  center: { padding: spacing.xl, alignItems: 'center' },
  empty: { color: colors.textMuted, fontSize: 15 },
  footerLoader: { padding: spacing.md },
});
