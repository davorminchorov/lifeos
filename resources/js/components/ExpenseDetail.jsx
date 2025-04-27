import React, { useEffect, useState } from 'react';
import { subscribeToSingleExpense } from '../services/broadcasting';

export default function ExpenseDetail({ expenseId }) {
    const [expense, setExpense] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // Fetch expense details
    useEffect(() => {
        const fetchExpense = async () => {
            try {
                const response = await fetch(`/api/expenses/${expenseId}`);

                if (!response.ok) {
                    throw new Error('Failed to fetch expense');
                }

                const data = await response.json();
                setExpense(data);
                setIsLoading(false);
            } catch (error) {
                console.error('Error fetching expense:', error);
                setError(error.message);
                setIsLoading(false);
            }
        };

        fetchExpense();
    }, [expenseId]);

    // Subscribe to real-time updates for this specific expense
    useEffect(() => {
        if (!expense) return;

        // Subscribe to expense updates
        const subscription = subscribeToSingleExpense(expenseId, {
            onUpdated: (event) => {
                setExpense(prevExpense => ({ ...prevExpense, ...event.updates }));
            }
        });

        // Unsubscribe when component unmounts or expenseId changes
        return () => {
            subscription.unsubscribe();
        };
    }, [expenseId, expense]);

    if (isLoading) {
        return <div>Loading expense details...</div>;
    }

    if (error) {
        return <div className="error">Error: {error}</div>;
    }

    if (!expense) {
        return <div>Expense not found</div>;
    }

    return (
        <div className="expense-detail">
            <h2>{expense.description}</h2>
            <div className="expense-info">
                <p className="amount">${expense.amount}</p>
                <p className="date">Date: {expense.date}</p>
                <p className="category">Category: {expense.category_id ? expense.category_id : 'Uncategorized'}</p>
                {expense.notes && <p className="notes">Notes: {expense.notes}</p>}
            </div>
        </div>
    );
}
