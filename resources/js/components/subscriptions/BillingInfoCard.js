import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
const BillingInfoCard = ({ billingInfo, onUpdatePaymentMethod, onUpdateBillingAddress, }) => {
    const { paymentMethod, billingAddress } = billingInfo;
    const getCardIcon = (brand) => {
        switch (brand === null || brand === void 0 ? void 0 : brand.toLowerCase()) {
            case 'visa':
                return '💳 Visa';
            case 'mastercard':
                return '💳 Mastercard';
            case 'amex':
            case 'american express':
                return '💳 American Express';
            default:
                return '💳 Card';
        }
    };
    return (_jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsx("h3", { className: "text-xl font-semibold text-on-surface mb-3", children: "Payment Method" }), paymentMethod ? (_jsx("div", { className: "bg-surface-variant/30 p-4 rounded-lg", children: _jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { children: [_jsxs("p", { className: "font-medium text-on-surface", children: [getCardIcon(paymentMethod.brand), paymentMethod.last4 && ` •••• ${paymentMethod.last4}`] }), paymentMethod.expiryDate && (_jsxs("p", { className: "text-sm text-on-surface-variant mt-1", children: ["Expires ", paymentMethod.expiryDate] }))] }), onUpdatePaymentMethod && (_jsx("button", { onClick: onUpdatePaymentMethod, className: "text-sm text-primary hover:text-primary/80 font-medium", children: "Update" }))] }) })) : (_jsxs("div", { className: "border-2 border-dashed border-outline/30 p-4 rounded-lg text-center", children: [_jsx("p", { className: "text-on-surface-variant", children: "No payment method on file" }), onUpdatePaymentMethod && (_jsx("button", { onClick: onUpdatePaymentMethod, className: "mt-2 text-sm text-primary hover:text-primary/80 font-medium", children: "Add Payment Method" }))] }))] }), _jsxs("div", { children: [_jsx("h3", { className: "text-xl font-semibold text-on-surface mb-3", children: "Billing Address" }), billingAddress ? (_jsx("div", { className: "bg-surface-variant/30 p-4 rounded-lg", children: _jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { children: [_jsx("p", { className: "font-medium text-on-surface", children: billingAddress.name }), _jsxs("div", { className: "text-sm text-on-surface-variant mt-1 space-y-0.5", children: [_jsx("p", { children: billingAddress.addressLine1 }), billingAddress.addressLine2 && _jsx("p", { children: billingAddress.addressLine2 }), _jsxs("p", { children: [billingAddress.city, billingAddress.state && `, ${billingAddress.state}`, " ", billingAddress.postalCode] }), _jsx("p", { children: billingAddress.country })] })] }), onUpdateBillingAddress && (_jsx("button", { onClick: onUpdateBillingAddress, className: "text-sm text-primary hover:text-primary/80 font-medium", children: "Update" }))] }) })) : (_jsxs("div", { className: "border-2 border-dashed border-outline/30 p-4 rounded-lg text-center", children: [_jsx("p", { className: "text-on-surface-variant", children: "No billing address on file" }), onUpdateBillingAddress && (_jsx("button", { onClick: onUpdateBillingAddress, className: "mt-2 text-sm text-primary hover:text-primary/80 font-medium", children: "Add Billing Address" }))] }))] })] }));
};
export default BillingInfoCard;
