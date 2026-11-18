import React, { useState, useMemo } from 'react';

export default function DataTable({
    columns,
    data = [],
    title,
    subtitle,
    searchPlaceholder = 'Search...',
    onAdd,
    addButtonLabel = 'Add New',
    actions,
    extraHeaderActions,
    filterTabs = [],
    activeTab = 'all',
    onTabChange,
    loading = false,
}) {
    const [search, setSearch] = useState('');
    const [sortColumn, setSortColumn] = useState(null);
    const [sortDirection, setSortDirection] = useState('desc'); // 'asc' or 'desc'
    const [currentPage, setCurrentPage] = useState(1);
    const [pageSize, setPageSize] = useState(10);

    // Filtering by search query
    const filteredData = useMemo(() => {
        if (!search.trim()) return data;
        const q = search.toLowerCase();
        return data.filter((item) => {
            return columns.some((col) => {
                const val = col.accessor ? item[col.accessor] : col.searchVal ? col.searchVal(item) : '';
                return val ? String(val).toLowerCase().includes(q) : false;
            });
        });
    }, [data, search, columns]);

    // Sorting
    const sortedData = useMemo(() => {
        if (!sortColumn) return filteredData;
        const sorted = [...filteredData];
        sorted.sort((a, b) => {
            const valA = a[sortColumn];
            const valB = b[sortColumn];

            if (valA === valB) return 0;
            if (valA === null || valA === undefined) return 1;
            if (valB === null || valB === undefined) return -1;

            if (typeof valA === 'number' && typeof valB === 'number') {
                return sortDirection === 'asc' ? valA - valB : valB - valA;
            }
            const strA = String(valA).toLowerCase();
            const strB = String(valB).toLowerCase();
            return sortDirection === 'asc' ? strA.localeCompare(strB) : strB.localeCompare(strA);
        });
        return sorted;
    }, [filteredData, sortColumn, sortDirection]);

    // Pagination
    const totalItems = sortedData.length;
    const totalPages = Math.ceil(totalItems / pageSize) || 1;
    const paginatedData = useMemo(() => {
        const start = (currentPage - 1) * pageSize;
        return sortedData.slice(start, start + pageSize);
    }, [sortedData, currentPage, pageSize]);

    const handleSort = (accessor) => {
        if (!accessor) return;
        if (sortColumn === accessor) {
            setSortDirection((prev) => (prev === 'asc' ? 'desc' : 'asc'));
        } else {
            setSortColumn(accessor);
            setSortDirection('asc');
        }
    };

    return (
        <section className="admin-card">
            {/* Table Header Bar */}
            <div className="admin-card-head">
                <div>
                    <h2>{title}</h2>
                    {subtitle && <p className="text-muted-custom small mb-0 mt-1">{subtitle}</p>}
                </div>

                <div className="head-search-wrap flex-grow-1">
                    <div className="position-relative d-flex align-items-center">
                        <i
                            className="bi bi-search position-absolute text-muted-custom"
                            style={{ left: '14px', pointerEvents: 'none', zIndex: 2 }}
                        ></i>
                        <input
                            type="search"
                            className="form-control"
                            style={{
                                paddingLeft: '40px',
                                paddingRight: search ? '36px' : '14px',
                                height: '42px',
                                borderRadius: '12px',
                                background: 'var(--surface-2)',
                                border: '1px solid var(--line)',
                                color: 'var(--text)',
                            }}
                            placeholder={searchPlaceholder}
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setCurrentPage(1);
                            }}
                        />
                        {search && (
                            <button
                                type="button"
                                className="position-absolute border-0 bg-transparent text-muted-custom"
                                style={{ right: '10px', zIndex: 2, cursor: 'pointer' }}
                                onClick={() => setSearch('')}
                                aria-label="Clear search"
                            >
                                <i className="bi bi-x-circle-fill"></i>
                            </button>
                        )}
                    </div>
                </div>

                <div className="responsive-actions">
                    {extraHeaderActions}
                    {onAdd && (
                        <button type="button" className="btn btn-accent btn-sm" onClick={onAdd}>
                            <i className="bi bi-plus-lg me-1"></i>{addButtonLabel}
                        </button>
                    )}
                </div>
            </div>

            {/* Quick Filter Tabs */}
            {filterTabs.length > 0 && (
                <div className="d-flex flex-wrap gap-2 px-4 py-2 border-bottom" style={{ background: 'var(--surface-2)' }}>
                    {filterTabs.map((tab) => (
                        <button
                            key={tab.id}
                            type="button"
                            className={`btn btn-sm ${activeTab === tab.id ? 'btn-accent' : 'btn-outline-theme'}`}
                            style={{ borderRadius: '8px', padding: '4px 12px', fontSize: '0.8rem' }}
                            onClick={() => {
                                if (onTabChange) onTabChange(tab.id);
                                setCurrentPage(1);
                            }}
                        >
                            {tab.label}
                            {tab.count !== undefined && (
                                <span className={`badge ms-2 ${activeTab === tab.id ? 'bg-black text-white' : 'bg-secondary-subtle text-secondary'}`}>
                                    {tab.count}
                                </span>
                            )}
                        </button>
                    ))}
                </div>
            )}

            {/* Table Body */}
            <div className="admin-list-results">
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-accent" role="status">
                            <span className="visually-hidden">Loading data...</span>
                        </div>
                    </div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    {columns.map((col, idx) => (
                                        <th
                                            key={idx}
                                            onClick={() => col.sortable !== false && col.accessor && handleSort(col.accessor)}
                                            style={{
                                                cursor: col.sortable !== false && col.accessor ? 'pointer' : 'default',
                                                userSelect: 'none',
                                                whiteSpace: 'nowrap',
                                                padding: '14px 18px',
                                            }}
                                            className={col.className || ''}
                                        >
                                            {col.header}
                                            {col.sortable !== false && col.accessor && (
                                                <span className="ms-1 text-muted-custom">
                                                    {sortColumn === col.accessor ? (
                                                        sortDirection === 'asc' ? <i className="bi bi-arrow-up text-accent"></i> : <i className="bi bi-arrow-down text-accent"></i>
                                                    ) : (
                                                        <i className="bi bi-arrow-down-up opacity-25"></i>
                                                    )}
                                                </span>
                                            )}
                                        </th>
                                    ))}
                                    {actions && <th className="text-end" style={{ whiteSpace: 'nowrap', padding: '14px 18px' }}>Actions</th>}
                                </tr>
                            </thead>
                            <tbody>
                                {paginatedData.length === 0 ? (
                                    <tr>
                                        <td colSpan={columns.length + (actions ? 1 : 0)} className="text-center py-5">
                                            <div className="text-muted-custom py-3">
                                                <i className="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                                No matching records found.
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    paginatedData.map((item, rowIdx) => (
                                        <tr key={item.id || rowIdx}>
                                            {columns.map((col, colIdx) => (
                                                <td key={colIdx} className={col.className || ''} style={{ padding: '12px 18px' }}>
                                                    {col.render ? col.render(item, rowIdx) : item[col.accessor]}
                                                </td>
                                            ))}
                                            {actions && (
                                                <td className="text-end" style={{ padding: '12px 18px' }}>
                                                    <div className="d-flex justify-content-end gap-1">
                                                        {actions(item)}
                                                    </div>
                                                </td>
                                            )}
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination Controls */}
                {!loading && totalItems > 0 && (
                    <div className="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 p-3 border-top">
                        <div className="small text-muted-custom">
                            Showing {((currentPage - 1) * pageSize) + 1} to {Math.min(currentPage * pageSize, totalItems)} of {totalItems} entries
                        </div>

                        <div className="d-flex align-items-center gap-2">
                            <select
                                className="form-select form-select-sm"
                                style={{ width: '85px', borderRadius: '8px' }}
                                value={pageSize}
                                onChange={(e) => {
                                    setPageSize(Number(e.target.value));
                                    setCurrentPage(1);
                                }}
                            >
                                <option value={10}>10</option>
                                <option value={25}>25</option>
                                <option value={50}>50</option>
                                <option value={100}>100</option>
                            </select>

                            <nav aria-label="Table pagination">
                                <ul className="pagination pagination-sm mb-0">
                                    <li className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
                                        <button
                                            className="page-link"
                                            type="button"
                                            onClick={() => setCurrentPage((p) => Math.max(p - 1, 1))}
                                            aria-label="Previous"
                                        >
                                            <i className="bi bi-chevron-left"></i>
                                        </button>
                                    </li>

                                    {Array.from({ length: Math.min(totalPages, 5) }, (_, i) => {
                                        let pageNum = i + 1;
                                        if (totalPages > 5 && currentPage > 3) {
                                            pageNum = currentPage - 3 + i;
                                            if (pageNum > totalPages) pageNum = totalPages - (4 - i);
                                        }
                                        return (
                                            <li key={pageNum} className={`page-item ${currentPage === pageNum ? 'active' : ''}`}>
                                                <button
                                                    className="page-link"
                                                    type="button"
                                                    onClick={() => setCurrentPage(pageNum)}
                                                >
                                                    {pageNum}
                                                </button>
                                            </li>
                                        );
                                    })}

                                    <li className={`page-item ${currentPage === totalPages ? 'disabled' : ''}`}>
                                        <button
                                            className="page-link"
                                            type="button"
                                            onClick={() => setCurrentPage((p) => Math.min(p + 1, totalPages))}
                                            aria-label="Next"
                                        >
                                            <i className="bi bi-chevron-right"></i>
                                        </button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
}
