import axios from 'axios';
class InvestmentService {
    constructor() {
        this.baseUrl = '/api';
    }
    // Portfolio-level operations
    async getPortfolioSummary() {
        const response = await axios.get(`${this.baseUrl}/portfolio/summary`);
        return response.data;
    }
    // Investment operations
    async getAllInvestments() {
        const response = await axios.get(`${this.baseUrl}/investments`);
        return response.data;
    }
    async getInvestment(id) {
        const response = await axios.get(`${this.baseUrl}/investments/${id}`);
        return response.data.investment || response.data;
    }
    async createInvestment(data) {
        const response = await axios.post(`${this.baseUrl}/investments`, data);
        return response.data;
    }
    async updateInvestment(id, data) {
        const response = await axios.put(`${this.baseUrl}/investments/${id}`, data);
        return response.data;
    }
    async deleteInvestment(id) {
        await axios.delete(`${this.baseUrl}/investments/${id}`);
    }
    async getInvestmentPerformance(id) {
        const response = await axios.get(`${this.baseUrl}/investments/${id}/performance`);
        return response.data;
    }
    // Transaction operations
    async getTransactions(filters) {
        const params = new URLSearchParams();
        if (filters) {
            Object.entries(filters).forEach(([key, value]) => {
                if (value !== undefined && value !== null && value !== '') {
                    params.append(key, String(value));
                }
            });
        }
        const response = await axios.get(`${this.baseUrl}/transactions?${params.toString()}`);
        return response.data;
    }
    async getInvestmentTransactions(investmentId) {
        const response = await axios.get(`${this.baseUrl}/investments/${investmentId}/transactions`);
        return response.data;
    }
    async createTransaction(investmentId, data) {
        const response = await axios.post(`${this.baseUrl}/investments/${investmentId}/transactions`, data);
        return response.data;
    }
    async updateTransaction(investmentId, transactionId, data) {
        const response = await axios.put(`${this.baseUrl}/investments/${investmentId}/transactions/${transactionId}`, data);
        return response.data;
    }
    async deleteTransaction(investmentId, transactionId) {
        await axios.delete(`${this.baseUrl}/investments/${investmentId}/transactions/${transactionId}`);
    }
    // Valuation operations
    async getInvestmentValuations(investmentId) {
        const response = await axios.get(`${this.baseUrl}/investments/${investmentId}/valuations`);
        return response.data;
    }
    async createValuation(investmentId, data) {
        const response = await axios.post(`${this.baseUrl}/investments/${investmentId}/valuations`, data);
        return response.data;
    }
    async updateValuation(investmentId, valuationId, data) {
        const response = await axios.put(`${this.baseUrl}/investments/${investmentId}/valuations/${valuationId}`, data);
        return response.data;
    }
    async deleteValuation(investmentId, valuationId) {
        await axios.delete(`${this.baseUrl}/investments/${investmentId}/valuations/${valuationId}`);
    }
}
export default new InvestmentService();
