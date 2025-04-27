import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogBody, DialogFooter } from './Dialog';
export const Modal = ({ title, children, footer, onClose, size = 'md', open = true, hideCloseButton = false, fullHeight = false }) => {
    return (_jsx(Dialog, { open: open, onOpenChange: (isOpen) => !isOpen && onClose(), children: _jsxs(DialogContent, { size: size, hideCloseButton: hideCloseButton, fullHeight: fullHeight, children: [title && (_jsx(DialogHeader, { children: _jsx(DialogTitle, { children: title }) })), _jsx(DialogBody, { className: footer ? undefined : "flex-grow", children: children }), footer && (_jsx(DialogFooter, { children: footer }))] }) }));
};
export default Modal;
