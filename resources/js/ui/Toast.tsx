import React, { createContext, useContext, useState, ReactNode } from 'react';
import { cn } from '../utils/cn';
import { X } from 'lucide-react';

type ToastVariant = 'default' | 'destructive' | 'success';

interface Toast {
  id: string;
  title?: string;
  description?: string;
  variant?: ToastVariant;
  duration?: number;
}

interface ToastContextType {
  toasts: Toast[];
  toast: (toast: Omit<Toast, 'id'>) => void;
  dismiss: (id: string) => void;
}

const ToastContext = createContext<ToastContextType | undefined>(undefined);

export function useToast() {
  const context = useContext(ToastContext);

  if (!context) {
    throw new Error('useToast must be used within a ToastProvider');
  }

  return context;
}

interface ToastProviderProps {
  children: ReactNode;
}

export const ToastProvider: React.FC<ToastProviderProps> = ({ children }) => {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const toast = ({ title, description, variant = 'default', duration = 5000 }: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 9);
    const newToast = { id, title, description, variant, duration };

    setToasts((prev) => [...prev, newToast]);

    if (duration > 0) {
      setTimeout(() => {
        dismiss(id);
      }, duration);
    }

    return id;
  };

  const dismiss = (id: string) => {
    setToasts((prev) => prev.filter((toast) => toast.id !== id));
  };

  return (
    <ToastContext.Provider value={{ toasts, toast, dismiss }}>
      {children}
      {toasts.length > 0 && (
        <div className="fixed bottom-0 right-0 p-4 z-50 flex flex-col gap-2">
          {toasts.map((toast) => (
            <ToastComponent key={toast.id} toast={toast} onDismiss={() => dismiss(toast.id)} />
          ))}
        </div>
      )}
    </ToastContext.Provider>
  );
};

interface ToastComponentProps {
  toast: Toast;
  onDismiss: () => void;
}

const ToastComponent: React.FC<ToastComponentProps> = ({ toast, onDismiss }) => {
  const { title, description, variant = 'default' } = toast;

  return (
    <div
      className={cn(
        'pointer-events-auto flex w-full max-w-md rounded-lg shadow-elevation-3 border transition-all animate-in slide-in-from-bottom-5 duration-300',
        {
          'bg-surface text-on-surface border-outline/20': variant === 'default',
          'bg-error-container text-on-error-container border-error': variant === 'destructive',
          'bg-tertiary-container text-on-tertiary-container border-tertiary': variant === 'success',
        }
      )}
    >
      <div className="flex-1 p-4">
        {title && <div className="text-label-large font-medium">{title}</div>}
        {description && <div className="text-body-medium mt-1">{description}</div>}
      </div>
      <button
        onClick={onDismiss}
        className={cn(
          'flex-shrink-0 p-2 rounded-full my-2 mr-2 hover:bg-surface-variant/30',
          {
            'text-on-surface-variant': variant === 'default',
            'text-on-error-container': variant === 'destructive',
            'text-on-tertiary-container': variant === 'success',
          }
        )}
      >
        <X className="h-4 w-4" />
      </button>
    </div>
  );
};

export default ToastProvider;
