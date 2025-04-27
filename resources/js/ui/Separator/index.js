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
import * as React from "react";
import * as SeparatorPrimitive from "@radix-ui/react-separator";
import { cn } from "../../utils/cn";
const Separator = React.forwardRef((_a, ref) => {
    var { className, orientation = "horizontal", decorative = true } = _a, props = __rest(_a, ["className", "orientation", "decorative"]);
    return (_jsx(SeparatorPrimitive.Root, Object.assign({ ref: ref, decorative: decorative, orientation: orientation, className: cn("shrink-0 bg-border", orientation === "horizontal" ? "h-[1px] w-full" : "h-full w-[1px]", className) }, props)));
});
Separator.displayName = SeparatorPrimitive.Root.displayName;
export { Separator };
