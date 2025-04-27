// This is an adapter for xstate-store to ensure consistent usage across the app
import { createStore as xstateCreateStore } from '@xstate/store';
export const createStore = xstateCreateStore;
