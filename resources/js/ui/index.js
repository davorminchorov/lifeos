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
// Export UI components from their respective directories
export { default as Button } from './Button';
export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from './Card';
export { Badge } from './Badge';
export { Tabs } from './Tabs';
export { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogBody, DialogFooter, DialogTitle, DialogDescription, DialogOverlay, DialogPortal } from './Dialog';
export { Input } from './Input';
export { Label } from './Label';
export { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from './Select';
export { Separator } from './Separator';
export { Textarea } from './Textarea';
export { Table } from './Table';
export { Modal } from './Modal';
// Add any missing components
export const Spinner = ({ size = 'md' }) => {
    const sizeClasses = {
        sm: 'w-4 h-4',
        md: 'w-6 h-6',
        lg: 'w-8 h-8',
    };
    return (_jsx("div", { className: "flex justify-center items-center", children: _jsx("div", { className: `animate-spin rounded-full border-t-2 border-primary ${sizeClasses[size]}` }) }));
};
export const Heading = (_a) => {
    var { as: Component = 'h2', children, className = '' } = _a, props = __rest(_a, ["as", "children", "className"]);
    const baseClasses = 'font-bold tracking-tight text-on-surface';
    const sizeClasses = {
        h1: 'text-2xl sm:text-3xl',
        h2: 'text-xl sm:text-2xl',
        h3: 'text-lg sm:text-xl',
        h4: 'text-base sm:text-lg',
        h5: 'text-sm sm:text-base',
        h6: 'text-xs sm:text-sm',
    };
    return (_jsx(Component, Object.assign({ className: `${baseClasses} ${sizeClasses[Component]} ${className}` }, props, { children: children })));
};
