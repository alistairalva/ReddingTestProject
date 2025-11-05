
import type { ResourcesResponse, Level } from "./types";

const ENDPOINT = `${import.meta.env.API_BASE}/wp-json/test/v1/resources`;  //defined in .env file

export const DEV_AUTH_TOKEN = `${import.meta.env.DEV_AUTH_TOKEN}`;

export async function fetchResources(
  min_level: Level,
  auth = false
): Promise<ResourcesResponse> {
  const url = new URL(ENDPOINT);
  url.searchParams.set("min_level", min_level);
  const headers: Record<string, string> = {};
  if (auth) {
    headers["Authorization"] = `Bearer ${DEV_AUTH_TOKEN}`;
  }

  const res = await fetch(url.toString(), { headers });
  if (!res.ok) {
    throw new Error(`HTTP ${res.status}`);
  }
  const body = (await res.json()) as ResourcesResponse;
  return body;
}
