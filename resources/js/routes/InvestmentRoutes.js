import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Routes, Route } from 'react-router-dom';
import InvestmentsPage from '../pages/investments/InvestmentsPage';
import InvestmentsListPage from '../pages/investments/InvestmentsListPage';
import InvestmentDetailPage from '../pages/investments/InvestmentDetailPage';
import InvestmentCreatePage from '../pages/investments/InvestmentCreatePage';
import InvestmentEditPage from '../pages/investments/InvestmentEditPage';
import TransactionsPage from '../pages/investments/TransactionsPage';
const InvestmentRoutes = () => {
    return (_jsxs(Routes, { children: [_jsx(Route, { path: "/", element: _jsx(InvestmentsPage, {}) }), _jsx(Route, { path: "/list", element: _jsx(InvestmentsListPage, {}) }), _jsx(Route, { path: "/new", element: _jsx(InvestmentCreatePage, {}) }), _jsx(Route, { path: "/transactions", element: _jsx(TransactionsPage, {}) }), _jsx(Route, { path: "/:id", element: _jsx(InvestmentDetailPage, {}) }), _jsx(Route, { path: "/:id/edit", element: _jsx(InvestmentEditPage, {}) })] }));
};
export default InvestmentRoutes;
