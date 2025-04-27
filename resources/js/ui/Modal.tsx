import React from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from './Dialog';
import { X } from 'lucide-react';

export interface ModalProps {
  title?: string;
  children: React.ReactNode;
  onClose: () => void;
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
  open?: boolean;
}

export const Modal: React.FC<ModalProps> = ({
  title,
  children,
  onClose,
  size = 'md',
  open = true
}) => {
  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    full: 'max-w-full mx-4'
  };

  return (
    <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onClose()}>
      <DialogContent className={`${sizeClasses[size]} p-0 gap-0`}>
        <div className="flex items-center justify-between border-b p-4">
          <DialogHeader>
            <DialogTitle>{title}</DialogTitle>
          </DialogHeader>
          <button
            onClick={onClose}
            className="rounded-full p-1 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
            aria-label="Close"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
        <div className="p-4">
          {children}
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default Modal;
