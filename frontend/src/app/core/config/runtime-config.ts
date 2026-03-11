import { InjectionToken } from '@angular/core';

export interface RuntimeConfig {
  apiUrl: string;
}

declare global {
  interface Window {
    __REDERMIO_CONFIG__?: Partial<RuntimeConfig>;
  }
}

const defaultRuntimeConfig: RuntimeConfig = {
  apiUrl: 'http://localhost:8080/api',
};

export function getRuntimeConfig(): RuntimeConfig {
  return {
    ...defaultRuntimeConfig,
    ...(window.__REDERMIO_CONFIG__ ?? {}),
  };
}

export const APP_RUNTIME_CONFIG = new InjectionToken<RuntimeConfig>('APP_RUNTIME_CONFIG', {
  providedIn: 'root',
  factory: getRuntimeConfig,
});

export const API_BASE_URL = new InjectionToken<string>('API_BASE_URL', {
  providedIn: 'root',
  factory: () => getRuntimeConfig().apiUrl,
});
