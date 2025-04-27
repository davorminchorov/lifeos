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
import { cn } from '../../utils/cn';
function Card(_a) {
    var { className, variant = 'elevated', clickable = false, fullWidth = false } = _a, props = __rest(_a, ["className", "variant", "clickable", "fullWidth"]);
    const variantClasses = {
        elevated: 'bg-surface shadow-elevation-2 hover:shadow-elevation-3',
        filled: 'bg-surface-variant shadow-elevation-1',
        outlined: 'border-2 border-outline border-opacity-20 bg-surface hover:shadow-elevation-1',
    };
    return (_jsx("div", Object.assign({ className: cn('rounded-lg overflow-hidden', variantClasses[variant], clickable && 'cursor-pointer transition-shadow', fullWidth ? 'w-full' : '', className) }, props)));
}
function CardHeader(_a) {
    var { className, withBorder = false } = _a, props = __rest(_a, ["className", "withBorder"]);
    return (_jsx("div", Object.assign({ className: cn('px-6 py-4', withBorder && 'border-b border-outline border-opacity-20', className) }, props)));
}
function CardTitle(_a) {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("h3", Object.assign({ className: cn('text-title-medium font-medium text-on-surface leading-none', className) }, props)));
}
function CardDescription(_a) {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("p", Object.assign({ className: cn('text-body-medium text-on-surface-variant mt-1', className) }, props)));
}
function CardContent(_a) {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", Object.assign({ className: cn('px-6 py-4', className) }, props)));
}
function CardFooter(_a) {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", Object.assign({ className: cn('px-6 py-4 flex items-center border-t border-outline border-opacity-20', className) }, props)));
}
function CardActions(_a) {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", Object.assign({ className: cn('flex items-center justify-end space-x-2 px-6 py-4', className) }, props)));
}
// Export all components
export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter, CardActions };
