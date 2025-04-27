import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { CategoriesList } from '../components/expenses/CategoriesList';
import { Link } from 'react-router-dom';
import { Button } from '../ui';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/Card';
import { PageContainer } from '../ui/PageContainer';
export const CategoriesPage = () => {
    const [refreshTrigger, setRefreshTrigger] = useState(0);
    return (_jsxs(PageContainer, { title: "Expense Categories", subtitle: "Manage the categories used to organize your expenses", actions: _jsx(Link, { to: "/expenses", children: _jsx(Button, { variant: "outlined", icon: _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z", clipRule: "evenodd" }) }), children: "Back to Expenses" }) }), children: [_jsxs("div", { className: "flex space-x-4 mb-6", children: [_jsx(Link, { to: "/expenses", children: _jsx(Button, { variant: "text", children: "View Expenses" }) }), _jsx(Link, { to: "/budgets", children: _jsx(Button, { variant: "text", children: "Manage Budgets" }) })] }), _jsxs(Card, { variant: "elevated", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Categories" }) }), _jsx(CardContent, { children: _jsx(CategoriesList, { refreshTrigger: refreshTrigger }) })] })] }));
};
