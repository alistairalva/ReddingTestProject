import type { ResourceItem } from "../types";

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

export default Card;