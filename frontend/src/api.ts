import type { ResourcesResponse, Level } from "./types";

const ENDPOINT = `${import.meta.env.VITE_API_BASE}/wp-json/test/v1/resources`; //defined in .env file
export const DEV_AUTH_TOKEN = `${import.meta.env.VITE_SECRET_TOKEN}`;

// Only log in dev mode (Vite: import.meta.env.DEV)
const isDev = Boolean((import.meta.env as any).DEV || import.meta.env.MODE !== 'production');
const log = (...args: any[]) => { if (isDev) console.log(...args); };

export async function fetchResources(
  min_level: Level,
  isAuth: boolean
): Promise<ResourcesResponse> {
  const url = new URL(ENDPOINT);
  url.searchParams.set("min_level", min_level);


  const headers: Record<string, string> = {};
  if (isAuth) {
    headers["Authorization"] = `Bearer ${DEV_AUTH_TOKEN}`;
  }

  log('fetchResources ->', { url: url.toString(), headers }); //debug

  try {
  const res = await fetch(url.toString(), { headers });
  log('response status:', res.status, res.statusText); //debug

  const text = await res.text();
    log('response body (truncated)', text.slice(0, 1000));

  if (!res.ok) {
    throw new Error(`HTTP ${res.status}`);
  }

  const body = JSON.parse(text) as ResourcesResponse;
  log('parsed body ->', body); //debug
  return body;
} catch (error) {
  log('fetchResources error ->', { error });
  throw error;
}
}
