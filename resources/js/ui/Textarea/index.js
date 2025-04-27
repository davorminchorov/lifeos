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
import React, { useState } from 'react';
import { cn } from '../../utils/cn';
const Textarea = React.forwardRef((_a, ref) => {
    var { className, variant = 'outlined', error, helperText, label, fullWidth = true, id, value, placeholder, defaultValue, resize = 'vertical', onChange } = _a, props = __rest(_a, ["className", "variant", "error", "helperText", "label", "fullWidth", "id", "value", "placeholder", "defaultValue", "resize", "onChange"]);
    // Track focused state for label animation
    const [isFocused, setIsFocused] = useState(false);
    // Track if textarea has a value for label animation
    const [hasValue, setHasValue] = useState(Boolean(value || defaultValue || placeholder));
    // Generate a unique ID for the textarea if not provided
    const textareaId = id || `textarea-${Math.random().toString(36).substring(2, 9)}`;
    // Handle textarea focus
    const handleFocus = (e) => {
        var _a;
        setIsFocused(true);
        (_a = props.onFocus) === null || _a === void 0 ? void 0 : _a.call(props, e);
    };
    // Handle textarea blur
    const handleBlur = (e) => {
        var _a;
        setIsFocused(false);
        (_a = props.onBlur) === null || _a === void 0 ? void 0 : _a.call(props, e);
    };
    // Handle textarea changes
    const handleChange = (e) => {
        setHasValue(e.target.value !== '');
        onChange === null || onChange === void 0 ? void 0 : onChange(e);
    };
    // Base styles for all textareas
    const baseStyles = 'block transition-colors duration-200 ease-in-out';
    // Container styles
    const containerStyles = cn('relative', fullWidth ? 'w-full' : 'inline-block');
    // Variant styles
    const variantStyles = {
        outlined: cn('bg-transparent border rounded-sm focus:border-primary focus:ring-1 focus:ring-primary', error ? 'border-error' : 'border-surface-variant'),
        filled: cn('border-b border-t-0 border-l-0 border-r-0 rounded-t-sm bg-surface-variant/40 focus:bg-surface-variant/60', error ? 'border-error' : 'border-surface-variant'),
    };
    // Resize styles
    const resizeStyles = {
        none: 'resize-none',
        vertical: 'resize-y',
        horizontal: 'resize-x',
        both: 'resize',
    };
    // Label styles
    const labelBaseStyles = 'absolute pointer-events-none transition-all duration-200 ease-in-out';
    const labelActiveStyles = 'text-xs -translate-y-6';
    const labelInactiveStyles = 'text-surface-on-variant';
    const labelStyles = cn(labelBaseStyles, (isFocused || hasValue) ? labelActiveStyles : labelInactiveStyles, variant === 'outlined' ? 'px-1 left-3' : 'px-0 left-4', error ? 'text-error' : (isFocused ? 'text-primary' : 'text-surface-on-variant/70'));
    // Helper & error text styles
    const helperTextStyles = cn('text-xs mt-1', error ? 'text-error' : 'text-surface-on-variant/70');
    return (_jsxs("div", { className: containerStyles, children: [_jsxs("div", { className: "relative", children: [_jsx("textarea", Object.assign({ id: textareaId, ref: ref, className: cn(baseStyles, 'min-h-[80px] w-full p-4', variantStyles[variant], resizeStyles[resize], label ? 'pt-6' : '', className), placeholder: placeholder, onFocus: handleFocus, onBlur: handleBlur, onChange: handleChange, value: value, defaultValue: defaultValue }, props)), label && (_jsx("label", { htmlFor: textareaId, className: labelStyles, style: {
                            backgroundColor: variant === 'outlined' ? 'white' : 'transparent',
                            transform: `translateY(${(isFocused || hasValue) ? '-1.5rem' : '0.75rem'})`
                        }, children: label }))] }), (error || helperText) && (_jsx("div", { className: helperTextStyles, children: error || helperText }))] }));
});
Textarea.displayName = "Textarea";
export { Textarea };
