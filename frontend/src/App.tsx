import { useEffect, useMemo, useState } from "react";
import type { Level, ResourceItem } from "./types";
import { fetchResources, DEV_AUTH_TOKEN } from "./api";

const LEVELS: Level[] = ["beginner", "intermediate", "advanced"];

// function levelValue(level: Level): number {
//   return { beginner: 1, intermediate: 2, advanced: 3 }[level];
// }

function Card({ item }: { item: ResourceItem }) {
  return (
    <div className="bg-white rounded-2xl shadow-md p-6 md:p-8 max-w-xl text-gray-800">
      <h3 className="text-xl font-semibold text-shadow-md">{item.title}</h3>
      <div className="mt-2 text-sm text-gray-600">
        <span className="inline-block px-3 py-1 bg-gray-100 rounded-full text-xs mr-2">
          {item.level}
        </span>
        <span className="text-xs">Est. {item.reading_estimate} min</span>
      </div>
      <p className="mt-4 text-sm text-gray-700">
        {item.summary ?? (
          <em className="text-gray-400">Summary redacted (unauthenticated)</em>
        )}
      </p>
    </div>
  );
}

export default function App() {
  const [minLevel, setMinLevel] = useState<Level>("beginner");
  const [sortAsc, setSortAsc] = useState<boolean>(true);
  const [authenticated, setAuthenticated] = useState<boolean>(false);
  const [items, setItems] = useState<ResourceItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    setLoading(true);
    setError(null);
    try {
      const resp = await fetchResources(minLevel, authenticated);
      console.log("Fetch response:", resp);
      setItems(resp.items);
    } catch (e: any) {
      setError(e.message || "Error");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [minLevel, authenticated]);

  const sorted = useMemo(() => {
    const arr = [...items];
    arr.sort((a, b) =>
      sortAsc
        ? a.reading_estimate - b.reading_estimate
        : b.reading_estimate - a.reading_estimate
    );
    return arr;
  }, [items, sortAsc]);

  return (
    <div className="min-h-screen bg-gray-50 p-6 md:p-12">
      <header className="max-w-4xl mx-auto mb-8">
        <h1 className="text-3xl font-bold">Resources</h1>
        <p className="text-sm text-gray-600 mt-1">
          Demo: WordPress served resources + TypeScript frontend
        </p>
      </header>

      <main className="max-w-4xl mx-auto">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
          <div className="flex items-center gap-3">
            <label className="text-sm">Min level</label>
            <select
              value={minLevel}
              onChange={(e) => setMinLevel(e.target.value as Level)}
              className="p-2 rounded-md border"
            >
              {LEVELS.map((l) => (
                <option key={l} value={l}>
                  {l}
                </option>
              ))}
            </select>

            <label className="text-sm ml-4">Sort by reading estimate</label>
            <button
              onClick={() => setSortAsc((s) => !s)}
              className="px-3 py-2 rounded-md border"
            >
              {sortAsc ? "Ascending" : "Descending"}
            </button>
          </div>

          <div className="flex items-center gap-3">
            <label className="text-sm">Authenticated</label>
            <input
              type="checkbox"
              checked={authenticated}
              onChange={(e) => setAuthenticated(e.target.checked)}
            />
            <button
              className="px-3 py-2 rounded-md border"
              onClick={() => load()}
            >
              Refresh
            </button>
          </div>
        </div>

        {loading && <div className="text-gray-500">Loading...</div>}
        {error && <div className="text-red-500">Error: {error}</div>}

        <div className="grid gap-6 grid-cols-1 md:grid-cols-2">
          {sorted.map((item) => (
            <Card key={item.id} item={item} />
          ))}
        </div>
      </main>

      <footer className="max-w-4xl mx-auto mt-12 text-xs text-gray-500">
        <div>
          Auth token (dev): <code>{DEV_AUTH_TOKEN}</code>
        </div>
      </footer>
    </div>
  );
}
