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
import React from 'react';
import { cva } from 'class-variance-authority';
const alertVariants = cva("relative w-full rounded-lg border p-4 my-2 [&>svg~*]:pl-7 [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4", {
    variants: {
        type: {
            default: "bg-gray-50 text-gray-900 border-gray-200",
            error: "bg-red-50 text-red-900 border-red-500",
            warning: "bg-yellow-50 text-yellow-900 border-yellow-500",
            success: "bg-green-50 text-green-900 border-green-500"
        }
    },
    defaultVariants: {
        type: "default"
    }
});
const icons = {
    error: (_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", className: "w-5 h-5 text-red-600", children: _jsx("path", { fillRule: "evenodd", d: "M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z", clipRule: "evenodd" }) })),
    warning: (_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", className: "w-5 h-5 text-yellow-600", children: _jsx("path", { fillRule: "evenodd", d: "M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z", clipRule: "evenodd" }) })),
    success: (_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", className: "w-5 h-5 text-green-600", children: _jsx("path", { fillRule: "evenodd", d: "M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z", clipRule: "evenodd" }) })),
    default: null
};
export const Alert = React.forwardRef((_a, ref) => {
    var { className, type = "default", title, message } = _a, props = __rest(_a, ["className", "type", "title", "message"]);
    // Type assertion to make TypeScript happy
    const alertType = (type || 'default');
    return (_jsxs("div", Object.assign({ ref: ref, className: alertVariants({ type, className }) }, props, { children: [icons[alertType], title && _jsx("h5", { className: "mb-1 font-medium leading-none tracking-tight", children: title }), _jsx("div", { className: title ? "text-sm opacity-90" : "", children: message })] })));
});
Alert.displayName = "Alert";
