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
import { jsx as _jsx } from "react/jsx-runtime";
import React from 'react';
import { cn } from '../../utils/cn';
const Badge = React.forwardRef((_a, ref) => {
    var { className, variant = 'default' } = _a, props = __rest(_a, ["className", "variant"]);
    const variantStyles = {
        default: 'bg-primary hover:bg-primary/80 border-transparent text-primary-foreground',
        secondary: 'bg-secondary hover:bg-secondary/80 border-transparent text-secondary-foreground',
        outline: 'text-foreground border-border',
        success: 'bg-green-100 text-green-800 hover:bg-green-200 border-transparent',
        warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 border-transparent',
        danger: 'bg-red-100 text-red-800 hover:bg-red-200 border-transparent',
    };
    return (_jsx("div", Object.assign({ ref: ref, className: cn("inline-flex items-center border px-2.5 py-0.5 text-xs font-semibold transition-colors rounded-full", variantStyles[variant], className) }, props)));
});
Badge.displayName = 'Badge';
export { Badge };
