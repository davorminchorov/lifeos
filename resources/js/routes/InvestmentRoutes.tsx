import React from 'react';
import { Routes, Route } from 'react-router-dom';
import InvestmentsPage from '../pages/investments/InvestmentsPage';
import InvestmentsListPage from '../pages/investments/InvestmentsListPage';
import InvestmentDetailPage from '../pages/investments/InvestmentDetailPage';
import InvestmentCreatePage from '../pages/investments/InvestmentCreatePage';
import InvestmentEditPage from '../pages/investments/InvestmentEditPage';
import TransactionsPage from '../pages/investments/TransactionsPage';

const InvestmentRoutes: React.FC = () => {
  return (
    <Routes>
      <Route path="/" element={<InvestmentsPage />} />
      <Route path="/list" element={<InvestmentsListPage />} />
      <Route path="/new" element={<InvestmentCreatePage />} />
      <Route path="/transactions" element={<TransactionsPage />} />
      <Route path="/:id" element={<InvestmentDetailPage />} />
      <Route path="/:id/edit" element={<InvestmentEditPage />} />
    </Routes>
  );
};

export default InvestmentRoutes;
