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
import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import React, { forwardRef } from 'react';
import { cn } from '../../utils/cn';
const Button = forwardRef((_a, ref) => {
    var { className, variant = 'filled', size = 'md', icon, iconPosition = 'left', fullWidth = false, asChild = false, isLoading = false, children } = _a, props = __rest(_a, ["className", "variant", "size", "icon", "iconPosition", "fullWidth", "asChild", "isLoading", "children"]);
    // Normalize legacy variants to their modern equivalents
    const normalizedVariant = variant === 'outline' ? 'outlined' :
        (variant === 'default' || variant === 'primary') ? 'contained' :
            (variant === 'secondary') ? 'tonal' :
                (variant === 'ghost') ? 'text' :
                    variant;
    const variantStyles = {
        filled: 'bg-primary text-on-primary hover:bg-primary/90 active:bg-primary/80 shadow-elevation-1',
        contained: 'bg-primary text-on-primary hover:bg-primary/90 active:bg-primary/80 shadow-elevation-1',
        tonal: 'bg-primary-container text-on-primary-container hover:bg-primary-container/90 active:bg-primary-container/80 shadow-elevation-1',
        outlined: 'border-2 border-primary bg-transparent text-primary hover:bg-primary/10 active:bg-primary/20',
        text: 'bg-transparent text-primary hover:bg-primary/10 active:bg-primary/20',
        elevated: 'bg-surface text-on-surface hover:bg-surface/90 active:bg-surface/80 shadow-elevation-2',
        link: 'bg-transparent text-primary underline hover:text-primary/90',
        destructive: 'bg-error text-on-error hover:bg-error/90 active:bg-error/80',
    };
    const sizeStyles = {
        sm: 'px-3 py-1.5 text-sm rounded-full',
        md: 'px-4 py-2 rounded-full',
        lg: 'px-6 py-3 text-lg rounded-full',
    };
    // Handle asChild rendering for React Router Link wrapping
    if (asChild && React.Children.count(children) === 1) {
        const child = React.Children.only(children);
        return React.cloneElement(child, Object.assign(Object.assign({}, child.props), { className: cn('inline-flex items-center justify-center font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary/25 disabled:opacity-50 disabled:pointer-events-none', variantStyles[normalizedVariant], sizeStyles[size], fullWidth && 'w-full', className, child.props.className) }));
    }
    return (_jsx("button", Object.assign({ ref: ref, className: cn('inline-flex items-center justify-center font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary/25 disabled:opacity-50 disabled:pointer-events-none', variantStyles[normalizedVariant], sizeStyles[size], fullWidth && 'w-full', className), disabled: isLoading || props.disabled }, props, { children: isLoading ? (_jsxs("span", { className: "flex items-center", children: [_jsxs("svg", { className: "animate-spin -ml-1 mr-2 h-4 w-4", xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 24 24", children: [_jsx("circle", { className: "opacity-25", cx: "12", cy: "12", r: "10", stroke: "currentColor", strokeWidth: "4" }), _jsx("path", { className: "opacity-75", fill: "currentColor", d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" })] }), children] })) : (_jsxs(_Fragment, { children: [icon && iconPosition === 'left' && _jsx("span", { className: "mr-2", children: icon }), children, icon && iconPosition === 'right' && _jsx("span", { className: "ml-2", children: icon })] })) })));
});
Button.displayName = 'Button';
export { Button };
// For compatibility with existing code
export default Button;
