import axios from 'axios';

export interface Investment {
  id: string;
  name: string;
  type: string;
  institution: string;
  initial_investment: number;
  current_value: number;
  roi: number;
  start_date: string;
  description?: string;
  last_valuation_date: string;
  created_at: string;
  updated_at: string;
}

export interface Transaction {
  id: string;
  investment_id: string;
  investment_name?: string;
  type: 'deposit' | 'withdrawal' | 'dividend' | 'interest' | 'fee';
  amount: number;
  date: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface Valuation {
  id: string;
  investment_id: string;
  value: number;
  date: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface PortfolioSummary {
  total_invested: number;
  total_current_value: number;
  total_withdrawn: number;
  overall_roi: number;
  by_type: Record<string, { count: number; value: number; percentage: number }>;
  total_investments: number;
}

export interface InvestmentPerformance {
  roi: number;
  total_return: number;
  initial_value: number;
  current_value: number;
  total_invested: number;
  total_withdrawn: number;
  time_series: Array<{ date: string; value: number }>;
}

class InvestmentService {
  private baseUrl = '/api';

  // Portfolio-level operations
  async getPortfolioSummary(): Promise<PortfolioSummary> {
    const response = await axios.get(`${this.baseUrl}/portfolio/summary`);
    return response.data;
  }

  // Investment operations
  async getAllInvestments(): Promise<Investment[]> {
    const response = await axios.get(`${this.baseUrl}/investments`);
    return response.data;
  }

  async getInvestment(id: string): Promise<Investment> {
    const response = await axios.get(`${this.baseUrl}/investments/${id}`);
    return response.data.investment || response.data;
  }

  async createInvestment(data: Partial<Investment>): Promise<Investment> {
    const response = await axios.post(`${this.baseUrl}/investments`, data);
    return response.data;
  }

  async updateInvestment(id: string, data: Partial<Investment>): Promise<Investment> {
    const response = await axios.put(`${this.baseUrl}/investments/${id}`, data);
    return response.data;
  }

  async deleteInvestment(id: string): Promise<void> {
    await axios.delete(`${this.baseUrl}/investments/${id}`);
  }

  async getInvestmentPerformance(id: string): Promise<InvestmentPerformance> {
    const response = await axios.get(`${this.baseUrl}/investments/${id}/performance`);
    return response.data;
  }

  // Transaction operations
  async getTransactions(filters?: Record<string, any>): Promise<Transaction[]> {
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

  async getInvestmentTransactions(investmentId: string): Promise<Transaction[]> {
    const response = await axios.get(`${this.baseUrl}/investments/${investmentId}/transactions`);
    return response.data;
  }

  async createTransaction(
    investmentId: string,
    data: Omit<Transaction, 'id' | 'investment_id' | 'created_at' | 'updated_at'>
  ): Promise<Transaction> {
    const response = await axios.post(
      `${this.baseUrl}/investments/${investmentId}/transactions`,
      data
    );
    return response.data;
  }

  async updateTransaction(
    investmentId: string,
    transactionId: string,
    data: Partial<Transaction>
  ): Promise<Transaction> {
    const response = await axios.put(
      `${this.baseUrl}/investments/${investmentId}/transactions/${transactionId}`,
      data
    );
    return response.data;
  }

  async deleteTransaction(investmentId: string, transactionId: string): Promise<void> {
    await axios.delete(`${this.baseUrl}/investments/${investmentId}/transactions/${transactionId}`);
  }

  // Valuation operations
  async getInvestmentValuations(investmentId: string): Promise<Valuation[]> {
    const response = await axios.get(`${this.baseUrl}/investments/${investmentId}/valuations`);
    return response.data;
  }

  async createValuation(
    investmentId: string,
    data: Omit<Valuation, 'id' | 'investment_id' | 'created_at' | 'updated_at'>
  ): Promise<Valuation> {
    const response = await axios.post(
      `${this.baseUrl}/investments/${investmentId}/valuations`,
      data
    );
    return response.data;
  }

  async updateValuation(
    investmentId: string,
    valuationId: string,
    data: Partial<Valuation>
  ): Promise<Valuation> {
    const response = await axios.put(
      `${this.baseUrl}/investments/${investmentId}/valuations/${valuationId}`,
      data
    );
    return response.data;
  }

  async deleteValuation(investmentId: string, valuationId: string): Promise<void> {
    await axios.delete(`${this.baseUrl}/investments/${investmentId}/valuations/${valuationId}`);
  }
}

export default new InvestmentService();
