import React from 'react';
import { Routes, Route } from 'react-router-dom';
import InvestmentList from './InvestmentList';
import InvestmentDetail from './InvestmentDetail';
import InvestmentForm from './InvestmentForm';

const InvestmentRoutes: React.FC = () => {
  return (
    <Routes>
      <Route path="/" element={<InvestmentList />} />
      <Route path="/:id" element={<InvestmentDetail />} />
      <Route path="/create" element={<InvestmentForm isEditing={false} />} />
      <Route path="/:id/edit" element={<InvestmentForm isEditing={true} />} />
    </Routes>
  );
};

export default InvestmentRoutes;
