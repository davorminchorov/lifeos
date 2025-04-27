import { jsx as _jsx } from "react/jsx-runtime";
export const PageLayout = ({ children, className = "" }) => {
    return (_jsx("main", { className: `py-6 px-4 md:px-6 lg:px-8 ${className}`, children: _jsx("div", { className: "max-w-7xl mx-auto", children: children }) }));
};
export default PageLayout;
