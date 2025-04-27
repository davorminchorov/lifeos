import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
export const PageTitle = ({ title, description, icon }) => {
    return (_jsxs("div", { className: "flex items-start", children: [icon && (_jsx("div", { className: "mr-4 p-2 rounded-full bg-primary-container text-on-primary-container shadow-elevation-1", children: icon })), _jsxs("div", { children: [_jsx("h1", { className: "text-headline-large font-medium text-on-surface", children: title }), description && (_jsx("p", { className: "mt-1 text-body-large text-on-surface-variant", children: description }))] })] }));
};
export default PageTitle;
