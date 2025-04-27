import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Routes, Route } from 'react-router-dom';
import InvestmentList from './InvestmentList';
import InvestmentDetail from './InvestmentDetail';
import InvestmentForm from './InvestmentForm';
const InvestmentRoutes = () => {
    return (_jsxs(Routes, { children: [_jsx(Route, { path: "/", element: _jsx(InvestmentList, {}) }), _jsx(Route, { path: "/:id", element: _jsx(InvestmentDetail, {}) }), _jsx(Route, { path: "/create", element: _jsx(InvestmentForm, { isEditing: false }) }), _jsx(Route, { path: "/:id/edit", element: _jsx(InvestmentForm, { isEditing: true }) })] }));
};
export default InvestmentRoutes;
