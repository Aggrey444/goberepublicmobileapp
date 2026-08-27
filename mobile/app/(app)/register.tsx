import { router } from 'expo-router';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, View } from 'react-native';

import { Button } from '../../components/Button';
import { Input } from '../../components/Input';
import { colors, spacing } from '../../constants/theme';
import { ApiError } from '../../services/api';
import { register } from '../../services/auth';
import { useAuthStore } from '../../stores/authStore';

export default function RegisterScreen() {
  const signIn = useAuthStore((s) => s.signIn);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const submit = async () => {
    setError(null);
    if (password !== passwordConfirmation) {
      setError('Passwords do not match.');
      return;
    }
    setLoading(true);
    try {
      const data = await register({ name, email, phone, password, password_confirmation: passwordConfirmation });
      await signIn(data.token, data.user);
      router.replace('/(app)/(tabs)');
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Unable to create account.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.flex}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
        <Text style={styles.brand}>Create Account</Text>
        <Text style={styles.subtitle}>Join GOBE Republic</Text>

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <Input label="Full Name" value={name} onChangeText={setName} placeholder="Jane Doe" />
        <Input label="Email" value={email} onChangeText={setEmail} autoCapitalize="none" keyboardType="email-address" placeholder="you@example.com" />
        <Input label="Phone (optional)" value={phone} onChangeText={setPhone} keyboardType="phone-pad" placeholder="+234..." />
        <Input label="Password" value={password} onChangeText={setPassword} secureTextEntry placeholder="••••••••" />
        <Input label="Confirm Password" value={passwordConfirmation} onChangeText={setPasswordConfirmation} secureTextEntry placeholder="••••••••" />

        <Button title="Create Account" onPress={submit} loading={loading} />

        <View style={styles.footer}>
          <Text style={styles.footerText}>Already have an account?</Text>
          <Button title="Sign In" variant="ghost" onPress={() => router.replace('/(app)/login')} />
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.background },
  content: { flexGrow: 1, justifyContent: 'center', padding: spacing.lg },
  brand: { fontSize: 28, fontWeight: '800', color: colors.primaryDark, textAlign: 'center' },
  subtitle: { fontSize: 15, color: colors.textMuted, textAlign: 'center', marginBottom: spacing.xl, marginTop: spacing.xs },
  error: { color: colors.danger, textAlign: 'center', marginBottom: spacing.md },
  footer: { marginTop: spacing.lg, alignItems: 'center' },
  footerText: { color: colors.textMuted, fontSize: 14 },
});
