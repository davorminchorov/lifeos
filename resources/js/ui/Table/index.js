var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
import { jsx as _jsx } from "react/jsx-runtime";
import React from 'react';
import { cn } from '../../utils/cn';
const TableComponent = React.forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("div", { className: "w-full overflow-auto", children: _jsx("table", Object.assign({ ref: ref, className: cn("w-full caption-bottom text-sm", className) }, props)) }));
});
TableComponent.displayName = "Table";
const TableHeader = React.forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("thead", Object.assign({ ref: ref, className: cn("[&_tr]:border-b", className) }, props)));
});
TableHeader.displayName = "TableHeader";
const TableBody = React.forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("tbody", Object.assign({ ref: ref, className: cn("[&_tr:last-child]:border-0", className) }, props)));
});
TableBody.displayName = "TableBody";
const TableRow = React.forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("tr", Object.assign({ ref: ref, className: cn("border-b transition-colors hover:bg-gray-50/50 data-[state=selected]:bg-gray-50", className) }, props)));
});
TableRow.displayName = "TableRow";
// Renamed to HeaderCell to match usage in JobApplicationsPage
const TableHeaderCell = React.forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("th", Object.assign({ ref: ref, className: cn("h-12 px-4 text-left align-middle font-medium text-gray-500 [&:has([role=checkbox])]:pr-0", className) }, props)));
});
TableHeaderCell.displayName = "TableHeaderCell";
// Keep the original TableHead for backwards compatibility
const TableHead = TableHeaderCell;
const TableCell = React.forwardRef((_a, ref) => {
    var { className } = _a, props = __rest(_a, ["className"]);
    return (_jsx("td", Object.assign({ ref: ref, className: cn("p-4 align-middle [&:has([role=checkbox])]:pr-0", className) }, props)));
});
TableCell.displayName = "TableCell";
// Create a Table object with all components as properties
const Table = Object.assign(TableComponent, {
    Header: TableHeader,
    Body: TableBody,
    Row: TableRow,
    HeaderCell: TableHeaderCell,
    Cell: TableCell,
    Head: TableHead, // Also add the Head alias for backward compatibility
});
// Export both the compound component and individual components
export { Table, TableHeader, TableBody, TableRow, TableHead, TableHeaderCell, TableCell };
