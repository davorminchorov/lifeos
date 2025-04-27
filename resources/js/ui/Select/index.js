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
import React, { createContext, useContext, useState, forwardRef } from 'react';
import { cn } from '../../utils/cn';
import { ChevronDown } from 'lucide-react';
const SelectContext = createContext(undefined);
function useSelectContext() {
    const context = useContext(SelectContext);
    if (!context) {
        throw new Error('Select components must be used within a Select component');
    }
    return context;
}
// For backward compatibility with HTML select
export function Select(_a) {
    var { children, defaultValue = '', value: controlledValue, onValueChange, id = `select-${Math.random().toString(36).substring(2, 9)}`, disabled = false, label, error, helperText, variant = 'outlined', name, onChange, className } = _a, props = __rest(_a, ["children", "defaultValue", "value", "onValueChange", "id", "disabled", "label", "error", "helperText", "variant", "name", "onChange", "className"]);
    // Check if we're using the new compound component pattern or the legacy HTML select pattern
    const isLegacyMode = React.Children.toArray(children).some(child => React.isValidElement(child) && child.type === 'option');
    if (isLegacyMode) {
        // Render a standard HTML select for backward compatibility
        return (_jsxs("div", { className: cn("form-control", className), children: [label && (_jsx("label", { htmlFor: id, className: cn("block text-sm font-medium mb-1", error ? "text-error" : "text-on-surface"), children: label })), _jsx("select", Object.assign({ id: id, name: name, value: controlledValue, defaultValue: defaultValue, onChange: onChange, disabled: disabled, className: cn("block w-full px-3 py-2 text-sm rounded-md border focus:outline-none focus:ring-2 focus:ring-primary", variant === 'filled' ? "bg-surface-variant border-transparent" : "bg-transparent border-outline", error ? "border-error focus:ring-error" : "", disabled ? "opacity-50 cursor-not-allowed" : "") }, props, { children: children })), (error || helperText) && (_jsx("p", { className: cn("mt-1 text-sm", error ? "text-error" : "text-on-surface-variant"), children: error || helperText }))] }));
    }
    // Modern compound component implementation
    const [open, setOpen] = useState(false);
    const [uncontrolledValue, setUncontrolledValue] = useState(defaultValue);
    const isControlled = controlledValue !== undefined;
    const value = isControlled ? controlledValue : uncontrolledValue;
    const setValue = (newValue) => {
        if (!isControlled) {
            setUncontrolledValue(newValue);
        }
        onValueChange === null || onValueChange === void 0 ? void 0 : onValueChange(newValue);
        // Trigger onChange for compatibility
        if (onChange && name) {
            const event = {
                target: {
                    name,
                    value: newValue
                }
            };
            onChange(event);
        }
        setOpen(false);
    };
    return (_jsxs("div", { className: cn("form-control", className), children: [label && (_jsx("label", { htmlFor: id, className: cn("block text-sm font-medium mb-1", error ? "text-error" : "text-on-surface"), children: label })), _jsx(SelectContext.Provider, { value: { open, setOpen, value, setValue, id }, children: _jsx("div", { className: cn("relative", disabled && "opacity-50 pointer-events-none"), children: children }) }), (error || helperText) && (_jsx("p", { className: cn("mt-1 text-sm", error ? "text-error" : "text-on-surface-variant"), children: error || helperText }))] }));
}
export const SelectTrigger = forwardRef((_a, ref) => {
    var { className, placeholder = 'Select an option', variant = 'outlined' } = _a, props = __rest(_a, ["className", "placeholder", "variant"]);
    const { open, setOpen, value, id } = useSelectContext();
    return (_jsxs("button", Object.assign({ ref: ref, type: "button", role: "combobox", "aria-controls": `${id}-content`, "aria-expanded": open, "aria-haspopup": "listbox", className: cn("flex h-10 w-full items-center justify-between rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary", variant === 'filled'
            ? "bg-surface-variant border-transparent"
            : "bg-transparent border border-outline", "placeholder:text-on-surface-variant", className), onClick: () => setOpen(!open) }, props, { children: [_jsx(SelectValue, { placeholder: placeholder }), _jsx(ChevronDown, { className: cn("h-4 w-4 transition-transform duration-200", open && "rotate-180") })] })));
});
SelectTrigger.displayName = 'SelectTrigger';
export const SelectValue = ({ placeholder }) => {
    const { value } = useSelectContext();
    return (_jsx("span", { className: cn("block truncate", !value && "text-on-surface-variant"), children: value || placeholder }));
};
export const SelectContent = forwardRef((_a, ref) => {
    var { className, position = 'popper' } = _a, props = __rest(_a, ["className", "position"]);
    const { open, id } = useSelectContext();
    if (!open)
        return null;
    return (_jsx("div", Object.assign({ ref: ref, className: cn("absolute z-50 min-w-[8rem] bg-surface rounded-md border border-outline shadow-elevation-2 py-1 mt-1", position === 'popper' && "top-full left-0 w-full", className), id: `${id}-content`, role: "listbox" }, props)));
});
SelectContent.displayName = 'SelectContent';
export const SelectItem = forwardRef((_a, ref) => {
    var { className, children, value } = _a, props = __rest(_a, ["className", "children", "value"]);
    const { value: selectedValue, setValue } = useSelectContext();
    const isSelected = selectedValue === value;
    return (_jsx("li", Object.assign({ ref: ref, className: cn("relative flex w-full cursor-pointer select-none items-center py-2 px-3 text-sm outline-none", "hover:bg-primary-container hover:text-on-primary-container", "focus:bg-primary-container focus:text-on-primary-container", isSelected && "bg-primary-container text-on-primary-container", className), role: "option", "aria-selected": isSelected, "data-value": value, onClick: () => setValue(value) }, props, { children: children || value })));
});
SelectItem.displayName = 'SelectItem';
export const HTMLSelect = forwardRef((_a, ref) => {
    var { className, options } = _a, props = __rest(_a, ["className", "options"]);
    return (_jsx("select", Object.assign({ ref: ref, className: cn("h-10 w-full rounded-md border border-outline bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary", className) }, props, { children: options.map((option) => (_jsx("option", { value: option.value, children: option.label }, option.value))) })));
});
HTMLSelect.displayName = 'HTMLSelect';
