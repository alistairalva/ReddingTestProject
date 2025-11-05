export type Level = "beginner" | "intermediate" | "advanced";

export interface ResourceItem {
  id: number;
  title: string;
  summary: string | null;
  level: Level;
  reading_estimate: number;
}

export interface ResourcesResponse {
  success: boolean;
  min_level: Level;
  authenticated: boolean;
  items: ResourceItem[];
}
