/// <reference types="vite/client" />

interface ImportMeta {
    glob: (path: string) => Record<string, () => Promise<any>>;
}
