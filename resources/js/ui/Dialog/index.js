var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import React, { forwardRef, createContext, useContext, useState } from 'react';
import { cn } from '../../utils/cn';
import { X } from 'lucide-react';
const DialogContext = createContext(undefined);
function useDialogContext() {
    const context = useContext(DialogContext);
    if (!context) {
        throw new Error('Dialog components must be used within a Dialog component');
    }
    return context;
}
function Dialog({ children, open: controlledOpen, onOpenChange, defaultOpen = false, }) {
    const [uncontrolledOpen, setUncontrolledOpen] = useState(defaultOpen);
    const isControlled = controlledOpen !== undefined;
    const open = isControlled ? controlledOpen : uncontrolledOpen;
    const setOpen = (newOpen) => {
        if (!isControlled) {
            setUncontrolledOpen(newOpen);
        }
        onOpenChange === null || onOpenChange === void 0 ? void 0 : onOpenChange(newOpen);
    };
    return (_jsx(DialogContext.Provider, { value: { open, setOpen }, children: children }));
}
const DialogTrigger = forwardRef((_a, ref) => {
    var { onClick, asChild = false, children } = _a, props = __rest(_a, ["onClick", "asChild", "children"]);
    const { setOpen } = useDialogContext();
    const handleClick = (e) => {
        setOpen(true);
        onClick === null || onClick === void 0 ? void 0 : onClick(e);
    };
    if (asChild) {
        // Clone the child element with the necessary props
        const child = React.Children.only(children);
        return React.cloneElement(child, Object.assign({ onClick: handleClick, ref }, props));
    }
    return (_jsx("button", Object.assign({ type: "button", onClick: handleClick, ref: ref }, props, { children: children })));
});
DialogTrigger.displayName = 'DialogTrigger';
const DialogPortal = ({ children, className }) => {
    const { open } = useDialogContext();
    if (!open)
        return null;
    return (_jsx("div", { className: cn("fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50", className), children: children }));
};
DialogPortal.displayName = 'DialogPortal';
const DialogOverlay = forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", Object.assign({ ref: ref, className: cn("fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-all duration-100", className) }, props)));
});
DialogOverlay.displayName = 'DialogOverlay';
const DialogContent = forwardRef((_a, ref) => {
    var { className, children, onClose, size = 'md', hideCloseButton = false, fullHeight = false } = _a, props = __rest(_a, ["className", "children", "onClose", "size", "hideCloseButton", "fullHeight"]);
    const { setOpen } = useDialogContext();
    const handleClose = () => {
        if (onClose) {
            onClose();
        }
        else {
            setOpen(false);
        }
    };
    const sizeClasses = {
        xs: 'max-w-xs',
        sm: 'max-w-sm',
        md: 'max-w-lg',
        lg: 'max-w-2xl',
        xl: 'max-w-4xl',
        full: 'max-w-full mx-4'
    };
    return (_jsx(DialogPortal, { children: _jsxs("div", Object.assign({ className: cn("fixed left-[50%] top-[50%] z-50 grid w-full translate-x-[-50%] translate-y-[-50%] gap-4 bg-surface p-6 shadow-elevation-3 rounded-lg", sizeClasses[size], fullHeight && "h-[calc(100vh-2rem)]", className), ref: ref }, props, { children: [children, !hideCloseButton && (_jsxs("button", { className: "absolute right-4 top-4 rounded-full p-1 text-on-surface-variant/70 hover:bg-surface-variant focus:outline-none focus:ring-2 focus:ring-primary", onClick: handleClose, children: [_jsx(X, { className: "h-4 w-4" }), _jsx("span", { className: "sr-only", children: "Close" })] }))] })) }));
});
DialogContent.displayName = 'DialogContent';
const DialogHeader = forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", Object.assign({ ref: ref, className: cn("flex flex-col gap-1.5 text-left", className) }, props)));
});
DialogHeader.displayName = 'DialogHeader';
const DialogFooter = forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", Object.assign({ ref: ref, className: cn("flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 mt-4", className) }, props)));
});
DialogFooter.displayName = 'DialogFooter';
const DialogTitle = forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("h2", Object.assign({ ref: ref, className: cn("text-title-large font-medium leading-none", className) }, props)));
});
DialogTitle.displayName = 'DialogTitle';
const DialogDescription = forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("p", Object.assign({ ref: ref, className: cn("text-body-medium text-on-surface-variant", className) }, props)));
});
DialogDescription.displayName = 'DialogDescription';
const DialogBody = forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", Object.assign({ ref: ref, className: cn("py-2", className) }, props)));
});
DialogBody.displayName = 'DialogBody';
// Export all Dialog components
export { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription, DialogOverlay, DialogPortal, DialogBody, };
