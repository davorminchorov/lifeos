import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Link } from "react-router-dom";
import { Button } from "../ui";
export default function NotFound() {
    return (_jsx("div", { className: "container flex flex-col items-center justify-center min-h-screen py-12 bg-background", children: _jsxs("div", { className: "w-full max-w-md text-center", children: [_jsx("div", { className: "mb-8 flex justify-center", children: _jsx("div", { className: "w-12 h-12 rounded-full bg-primary flex items-center justify-center text-on-primary text-lg font-bold shadow-elevation-2", children: "L" }) }), _jsxs("div", { className: "bg-surface shadow-elevation-2 rounded-lg p-8", children: [_jsx("h1", { className: "text-6xl font-bold text-on-surface mb-2", children: "404" }), _jsx("h2", { className: "text-2xl font-semibold text-on-surface mb-4", children: "Page Not Found" }), _jsx("p", { className: "text-on-surface-variant mb-6", children: "The page you are looking for doesn't exist or has been moved." }), _jsx(Link, { to: "/", children: _jsx(Button, { variant: "filled", fullWidth: true, children: "Go Back Home" }) })] })] }) }));
}
