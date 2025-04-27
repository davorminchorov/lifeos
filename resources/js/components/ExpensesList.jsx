import React, { useEffect, useState } from 'react';
import { subscribeToExpenseEvents } from '../services/broadcasting';

export default function ExpensesList() {
    const [expenses, setExpenses] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    // Fetch expenses on component mount
    useEffect(() => {
        const fetchExpenses = async () => {
            try {
                const response = await fetch('/api/expenses');
                const data = await response.json();
                setExpenses(data);
                setIsLoading(false);
            } catch (error) {
                console.error('Error fetching expenses:', error);
                setIsLoading(false);
            }
        };

        fetchExpenses();
    }, []);

    // Subscribe to real-time expense updates
    useEffect(() => {
        // Subscribe to expense events
        const subscription = subscribeToExpenseEvents({
            onCreated: (event) => {
                setExpenses(prevExpenses => [...prevExpenses, event.expense]);
            },
            onUpdated: (event) => {
                setExpenses(prevExpenses =>
                    prevExpenses.map(expense =>
                        expense.id === event.expenseId ? { ...expense, ...event.updates } : expense
                    )
                );
            },
            onDeleted: (event) => {
                setExpenses(prevExpenses =>
                    prevExpenses.filter(expense => expense.id !== event.expenseId)
                );
            }
        });

        // Unsubscribe when component unmounts
        return () => {
            subscription.unsubscribe();
        };
    }, []);

    if (isLoading) {
        return <div>Loading expenses...</div>;
    }

    return (
        <div className="expenses-list">
            <h2>Expenses</h2>
            {expenses.length === 0 ? (
                <p>No expenses found.</p>
            ) : (
                <ul>
                    {expenses.map((expense) => (
                        <li key={expense.id}>
                            <h3>{expense.description}</h3>
                            <p>${expense.amount}</p>
                            <p>Date: {expense.date}</p>
                            {expense.notes && <p>Notes: {expense.notes}</p>}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
