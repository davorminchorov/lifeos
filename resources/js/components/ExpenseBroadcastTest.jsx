import React, { useEffect, useState } from 'react';
import { subscribeToExpenseEvents } from '../services/broadcasting';

export default function ExpenseBroadcastTest() {
    const [messages, setMessages] = useState([]);
    const [isConnected, setIsConnected] = useState(false);

    useEffect(() => {
        // First, check if Echo is properly initialized
        if (!window.Echo) {
            setMessages(prev => [...prev, 'Error: Echo is not initialized']);
            return;
        }

        try {
            // Add connection status listeners
            window.Echo.connector.pusher.connection.bind('connected', () => {
                setIsConnected(true);
                setMessages(prev => [...prev, 'Connected to Pusher!']);
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                setIsConnected(false);
                setMessages(prev => [...prev, 'Disconnected from Pusher']);
            });

            window.Echo.connector.pusher.connection.bind('error', (err) => {
                setMessages(prev => [...prev, `Connection error: ${JSON.stringify(err)}`]);
            });

            // Subscribe to expense events
            const subscription = subscribeToExpenseEvents({
                onCreated: (event) => {
                    setMessages(prev => [...prev, `Expense created: ${JSON.stringify(event.expense)}`]);
                },
                onUpdated: (event) => {
                    setMessages(prev => [...prev, `Expense updated: ${event.expenseId}`]);
                },
                onDeleted: (event) => {
                    setMessages(prev => [...prev, `Expense deleted: ${event.expenseId}`]);
                }
            });

            setMessages(prev => [...prev, 'Subscribed to expense events']);

            // Cleanup
            return () => {
                window.Echo.connector.pusher.connection.unbind('connected');
                window.Echo.connector.pusher.connection.unbind('disconnected');
                window.Echo.connector.pusher.connection.unbind('error');
                subscription.unsubscribe();
                setMessages(prev => [...prev, 'Cleaned up event listeners']);
            };
        } catch (error) {
            setMessages(prev => [...prev, `Error setting up broadcasting: ${error.message}`]);
        }
    }, []);

    return (
        <div className="broadcast-test p-4 border rounded">
            <h2 className="text-xl font-bold mb-4">Broadcasting Test</h2>

            <div className="connection-status mb-4">
                <p>
                    Status: <span className={isConnected ? 'text-green-500' : 'text-red-500'}>
                        {isConnected ? 'Connected' : 'Disconnected'}
                    </span>
                </p>
            </div>

            <div className="event-log border p-2 bg-gray-100 h-64 overflow-y-auto">
                <h3 className="font-semibold mb-2">Event Log:</h3>
                {messages.length === 0 ? (
                    <p className="text-gray-500">Waiting for events...</p>
                ) : (
                    <ul className="list-disc pl-5">
                        {messages.map((msg, index) => (
                            <li key={index} className="mb-1">{msg}</li>
                        ))}
                    </ul>
                )}
            </div>
        </div>
    );
}
