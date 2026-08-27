import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';

import Constants from 'expo-constants';
import { registerDeviceToken } from './device';

Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowBanner: true,
    shouldShowList: true,
    shouldPlaySound: false,
    shouldSetBadge: false,
  }),
});

async function requestPermissions(): Promise<boolean> {
  const current = await Notifications.getPermissionsAsync();
  if (current.granted) return true;
  const requested = await Notifications.requestPermissionsAsync();
  return requested.granted;
}

function canUsePush(): boolean {
  if (Constants.isDevice === false) {
    return false;
  }
  if (Platform.OS === 'android' && !Constants.expoConfig?.android?.package) {
    return false;
  }
  return true;
}

export async function registerPushToken(): Promise<boolean> {
  try {
    if (!canUsePush()) return false;
    const granted = await requestPermissions();
    if (!granted) return false;
    const tokenData = await Notifications.getExpoPushTokenAsync({
      projectId: Constants.expoConfig?.extra?.eas?.projectId,
    });
    await registerDeviceToken(tokenData.data);
    return true;
  } catch {
    return false;
  }
}

export function setupNotificationListener() {
  return Notifications.addNotificationReceivedListener(() => {});
}
