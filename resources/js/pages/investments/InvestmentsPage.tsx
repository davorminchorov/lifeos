import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { formatCurrency } from '../../utils/format';
import { PlusCircle, ArrowUpRight, DollarSign, PercentIcon, Briefcase, PieChart, BarChart, ArrowRight } from 'lucide-react';

interface PortfolioSummary {
  total_invested: number;
  total_current_value: number;
  total_withdrawn: number;
  overall_roi: number;
  by_type: Record<string, { count: number; value: number; percentage: number }>;
  total_investments: number;
}

const InvestmentsPage: React.FC = () => {
  const [summary, setSummary] = useState<PortfolioSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchSummary = async () => {
      try {
        setLoading(true);
        const response = await axios.get('/api/portfolio/summary');
        setSummary(response.data);
      } catch (err) {
        console.error('Failed to fetch portfolio summary', err);
        setError('Failed to load portfolio data');
      } finally {
        setLoading(false);
      }
    };

    fetchSummary();
  }, []);

  return (
    <PageContainer
      title="Investments"
      subtitle="Manage and track your investments portfolio"
      actions={
        <Button
          variant="filled"
          icon={<PlusCircle className="h-4 w-4 mr-2" />}
        >
          <Link to="/investments/new">Add Investment</Link>
        </Button>
      }
    >
      {error && (
        <div className="mb-6">
          <Card variant="elevated">
            <CardContent>
              <div className="bg-error/10 text-error p-4 rounded-lg">
                {error}
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      {loading ? (
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      ) : (
        <>
          <PageSection>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <Card variant="elevated">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <div className="flex-shrink-0 p-3 bg-primary/10 rounded-full">
                        <DollarSign className="h-6 w-6 text-primary" />
                      </div>
                      <div>
                        <p className="text-on-surface-variant text-sm mb-1">Total Invested</p>
                        <p className="text-on-surface text-2xl font-bold">
                          {formatCurrency(summary?.total_invested || 0, 'USD')}
                        </p>
                      </div>
                    </div>
                    <ArrowUpRight className="h-5 w-5 text-primary" />
                  </div>
                </CardContent>
              </Card>

              <Card variant="elevated">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <div className="flex-shrink-0 p-3 bg-success/10 rounded-full">
                        <DollarSign className="h-6 w-6 text-success" />
                      </div>
                      <div>
                        <p className="text-on-surface-variant text-sm mb-1">Current Value</p>
                        <p className="text-on-surface text-2xl font-bold">
                          {formatCurrency(summary?.total_current_value || 0, 'USD')}
                        </p>
                      </div>
                    </div>
                    <ArrowUpRight className="h-5 w-5 text-success" />
                  </div>
                </CardContent>
              </Card>

              <Card variant="elevated">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <div className="flex-shrink-0 p-3 bg-warning/10 rounded-full">
                        <PercentIcon className="h-6 w-6 text-warning" />
                      </div>
                      <div>
                        <p className="text-on-surface-variant text-sm mb-1">Overall ROI</p>
                        <p className="text-on-surface text-2xl font-bold">
                          {summary && summary.overall_roi !== undefined ? (summary.overall_roi > 0 ? '+' : '') + summary.overall_roi.toFixed(2) + '%' : '0.00%'}
                        </p>
                      </div>
                    </div>
                    <ArrowUpRight className="h-5 w-5 text-warning" />
                  </div>
                </CardContent>
              </Card>

              <Card variant="elevated">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                      <div className="flex-shrink-0 p-3 bg-secondary/10 rounded-full">
                        <Briefcase className="h-6 w-6 text-secondary" />
                      </div>
                      <div>
                        <p className="text-on-surface-variant text-sm mb-1">Total Investments</p>
                        <p className="text-on-surface text-2xl font-bold">
                          {summary?.total_investments ?? 0}
                        </p>
                      </div>
                    </div>
                    <ArrowUpRight className="h-5 w-5 text-secondary" />
                  </div>
                </CardContent>
              </Card>
            </div>
          </PageSection>

          {summary && summary.by_type && Object.keys(summary.by_type).length > 0 && (
            <PageSection title="Portfolio Allocation" className="mt-8">
              <Card variant="outlined">
                <CardContent className="p-6">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="flex items-center justify-center">
                      <PieChart className="h-48 w-48 text-primary opacity-20" />
                    </div>
                    <div>
                      <h3 className="text-lg font-semibold mb-4">Asset Allocation</h3>
                      <div className="space-y-4">
                        {Object.entries(summary.by_type).map(([type, data]) => (
                          <div key={type} className="flex items-center">
                            <div
                              className="w-3 h-3 rounded-full mr-3"
                              style={{
                                backgroundColor: getColorForAssetType(type)
                              }}
                            ></div>
                            <div className="flex-1">
                              <div className="flex justify-between items-center">
                                <span className="text-sm font-medium">{formatAssetType(type)}</span>
                                <span className="text-sm text-on-surface-variant">{data.percentage.toFixed(1)}%</span>
                              </div>
                              <div className="w-full bg-surface-variant h-1.5 rounded-full mt-1">
                                <div
                                  className="h-full rounded-full"
                                  style={{
                                    width: `${data.percentage}%`,
                                    backgroundColor: getColorForAssetType(type)
                                  }}
                                ></div>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </PageSection>
          )}

          <div className="mt-8 text-center">
            <Button
              variant="outlined"
              onClick={() => {}}
              icon={<ArrowRight className="h-4 w-4 ml-2" />}
              className="ml-auto"
            >
              <Link to="/investments/list">View All Investments</Link>
            </Button>
          </div>
        </>
      )}
    </PageContainer>
  );
};

// Helper functions for asset types
function formatAssetType(type: string): string {
  const typeMap: Record<string, string> = {
    stock: 'Stocks',
    bond: 'Bonds',
    mutual_fund: 'Mutual Funds',
    etf: 'ETFs',
    real_estate: 'Real Estate',
    retirement: 'Retirement',
    crypto: 'Cryptocurrency',
    cash: 'Cash & Savings',
    other: 'Other',
  };
  return typeMap[type] || type.charAt(0).toUpperCase() + type.slice(1);
}

function getColorForAssetType(type: string): string {
  const colorMap: Record<string, string> = {
    stock: '#4F46E5', // indigo
    bond: '#0891B2', // cyan
    mutual_fund: '#7C3AED', // violet
    etf: '#2563EB', // blue
    real_estate: '#16A34A', // green
    retirement: '#EA580C', // orange
    crypto: '#9333EA', // purple
    cash: '#65A30D', // lime
    other: '#94A3B8', // slate
  };
  return colorMap[type] || '#94A3B8';
}

export default InvestmentsPage;
