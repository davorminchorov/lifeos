import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
/**
 * PageContainer component that provides consistent page layout structure
 * following Material Design 3 principles with:
 * - Page title and optional subtitle
 * - Optional actions slot for buttons/controls
 * - Properly spaced content container
 */
export function PageContainer({ children, title, subtitle, actions }) {
    return (_jsxs("div", { className: "w-full max-w-screen-xl mx-auto", children: [_jsxs("div", { className: "mb-6 flex flex-col md:flex-row md:items-center md:justify-between", children: [_jsxs("div", { children: [_jsx("h1", { className: "text-headline-medium font-bold text-on-surface mb-1", children: title }), subtitle && (_jsx("p", { className: "text-body-large text-on-surface-variant", children: subtitle }))] }), actions && (_jsx("div", { className: "mt-4 md:mt-0 flex flex-wrap gap-2", children: actions }))] }), _jsx("div", { className: "bg-surface rounded-lg shadow-elevation-1 p-4 md:p-6 border border-outline border-opacity-10", children: children })] }));
}
/**
 * PageSection component for dividing page content into logical sections
 */
export function PageSection({ children, title, subtitle, className = '' }) {
    return (_jsxs("section", { className: `mb-8 ${className}`, children: [(title || subtitle) && (_jsxs("div", { className: "mb-4 border-b border-outline border-opacity-10 pb-2", children: [title && _jsx("h2", { className: "text-title-large font-medium text-on-surface mb-1", children: title }), subtitle && _jsx("p", { className: "text-body-medium text-on-surface-variant", children: subtitle })] })), children] }));
}
/**
 * PageGrid component for creating responsive grid layouts
 */
export function PageGrid({ children, columns = 1 }) {
    const gridCols = {
        1: 'grid-cols-1',
        2: 'grid-cols-1 md:grid-cols-2',
        3: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    };
    return (_jsx("div", { className: `grid ${gridCols[columns]} gap-4 md:gap-6`, children: children }));
}
export default PageContainer;
