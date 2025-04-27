// This is a custom implementation to replace the problematic xstate-store module
import { useState, useEffect } from 'react';

// Custom type definitions to match how stores are being used in the app
export interface StoreConfig<TContext> {
  name?: string; // Optional name property
  initialState: TContext;
  actions: Record<string, (state: TContext, payload?: any) => TContext>;
}

// Helper for making an object work with array destructuring
function makeIterable<T, A>(obj: T, actions: A): T & A & Iterable<T | A> {
  const iterableObj = obj as T & A & Iterable<T | A>;

  // Add Symbol.iterator method to make the object work with array destructuring
  iterableObj[Symbol.iterator] = function* () {
    yield obj;
    yield actions;
  };

  return iterableObj;
}

// Simple store implementation
export function createStore<TContext>(config: StoreConfig<TContext>) {
  const { initialState, actions } = config;

  // Create a global state object
  let state = { ...initialState };
  let listeners: (() => void)[] = [];

  // Method to notify all listeners
  const notify = () => {
    listeners.forEach(listener => listener());
  };

  // Create action dispatchers
  const dispatchers = Object.entries(actions).reduce((acc, [actionName, actionFn]) => {
    acc[actionName] = (payload?: any) => {
      state = actionFn(state, payload);
      notify();
    };
    return acc;
  }, {} as Record<string, (payload?: any) => void>);

  // Additional utility actions
  const extraActions = {
    setState: (newState: Partial<TContext>) => {
      state = { ...state, ...newState };
      notify();
    }
  };

  const allActions = { ...dispatchers, ...extraActions };

  // Hook to access store state and actions
  const useStore = () => {
    // Create a composite object with state, actions, and iterator support
    const [storeObj, setStoreObj] = useState(() =>
      makeIterable({ ...state }, allActions)
    );

    useEffect(() => {
      // Subscribe to state changes
      const handleChange = () => {
        setStoreObj(makeIterable({ ...state }, allActions));
      };

      listeners.push(handleChange);

      // Unsubscribe on unmount
      return () => {
        listeners = listeners.filter(listener => listener !== handleChange);
      };
    }, []);

    return storeObj;
  };

  return {
    useStore,
    dispatchers: allActions
  };
}
