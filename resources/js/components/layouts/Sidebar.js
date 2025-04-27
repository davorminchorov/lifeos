"use strict";
// Add expenses section to navigation items
const navigationItems = [
    // ... existing navigation items
    {
        name: 'Expenses',
        icon: 'dollar-sign', // or any appropriate icon
        current: false,
        children: [
            { name: 'Track Expenses', href: '/expenses', current: false },
            { name: 'Budgets', href: '/budgets', current: false },
            { name: 'Categories', href: '/categories', current: false },
        ],
    },
    {
        name: 'Job Applications',
        icon: 'briefcase', // or any appropriate icon
        current: false,
        href: '/job-applications',
    },
    // ... existing navigation items
];
