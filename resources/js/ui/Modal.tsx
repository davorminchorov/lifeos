import React from 'react';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogBody,
  DialogFooter
} from './Dialog';

/**
 * Modal component following Material Design guidelines
 *
 * This is a convenient wrapper around the Dialog component that provides a standardized layout
 * with a title, content area, and optional footer. It's designed to be used for common dialog patterns
 * like forms, confirmations, and alerts.
 */

export interface ModalProps {
  /**
   * The title displayed in the modal header
   */
  title?: React.ReactNode;

  /**
   * The content to display in the modal body
   */
  children: React.ReactNode;

  /**
   * Footer content, typically action buttons
   */
  footer?: React.ReactNode;

  /**
   * Callback fired when the modal is closed
   */
  onClose: () => void;

  /**
   * Controls the width of the modal
   * @default 'md'
   */
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'full';

  /**
   * Whether the modal is open
   * @default true
   */
  open?: boolean;

  /**
   * Whether to hide the close button
   * @default false
   */
  hideCloseButton?: boolean;

  /**
   * Whether the modal should take up the full height of the viewport
   * @default false
   */
  fullHeight?: boolean;
}

export const Modal: React.FC<ModalProps> = ({
  title,
  children,
  footer,
  onClose,
  size = 'md',
  open = true,
  hideCloseButton = false,
  fullHeight = false
}) => {
  return (
    <Dialog open={open} onOpenChange={(isOpen) => !isOpen && onClose()}>
      <DialogContent
        size={size}
        hideCloseButton={hideCloseButton}
        fullHeight={fullHeight}
      >
        {title && (
          <DialogHeader>
            <DialogTitle>{title}</DialogTitle>
          </DialogHeader>
        )}

        <DialogBody className={footer ? undefined : "flex-grow"}>
          {children}
        </DialogBody>

        {footer && (
          <DialogFooter>
            {footer}
          </DialogFooter>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default Modal;
