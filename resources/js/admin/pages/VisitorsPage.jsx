import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import DataTable from '../components/DataTable';

export default function VisitorsPage({ getApiBaseUrl }) {
    const [visitors, setVisitors] = useState([]);
    const [loading, setLoading] = useState(true);

    const loadVisitors = useCallback(() => {
        setLoading(true);
        axios.get(`${getApiBaseUrl()}/admin/visitors`)
            .then((res) => {
                setVisitors(res.data.visitors || []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [getApiBaseUrl]);

    useEffect(() => {
        loadVisitors();
    }, [loadVisitors]);

    const columns = [
        {
            header: 'Page / Path',
            accessor: 'path',
            render: (v) => (
                <div>
                    <strong>{v.path}</strong>
                    {v.route_name && <small className="text-muted-custom d-block">{v.route_name}</small>}
                </div>
            ),
        },
        {
            header: 'IP Address',
            accessor: 'ip_address',
            render: (v) => <span className="font-monospace small">{v.ip_address}</span>,
        },
        {
            header: 'Browser & OS',
            accessor: 'browser',
            render: (v) => (
                <div>
                    <div>{v.browser}</div>
                    <small className="text-muted-custom">{v.platform} &bull; {v.device_type}</small>
                </div>
            ),
        },
        {
            header: 'Visited At',
            accessor: 'visited_at',
            render: (v) => (
                <div>
                    <div>{v.visited_at}</div>
                    <small className="text-muted-custom">{v.visited_at_human}</small>
                </div>
            ),
        },
    ];

    return (
        <DataTable
            title="Real-Time Website Visitors Log"
            searchPlaceholder="Search by IP, path, browser, OS..."
            columns={columns}
            data={visitors}
            loading={loading}
            extraHeaderActions={
                <button type="button" className="btn btn-outline-theme btn-sm" onClick={loadVisitors}>
                    <i className="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            }
        />
    );
}
