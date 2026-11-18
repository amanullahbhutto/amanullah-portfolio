import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import DataTable from '../components/DataTable';

export default function UsersPage({ getApiBaseUrl }) {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);

    const loadUsers = useCallback(() => {
        setLoading(true);
        axios.get(`${getApiBaseUrl()}/admin/users`)
            .then((res) => {
                setUsers(res.data.users || []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [getApiBaseUrl]);

    useEffect(() => {
        loadUsers();
    }, [loadUsers]);

    const columns = [
        {
            header: 'User Name',
            accessor: 'name',
            render: (u) => (
                <div className="d-flex align-items-center gap-2">
                    <div
                        className="rounded-circle d-flex align-items-center justify-content-center text-accent fw-bold"
                        style={{ width: '36px', height: '36px', background: 'rgba(249, 115, 22, 0.1)' }}
                    >
                        {u.name?.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <strong>{u.name}</strong>
                    </div>
                </div>
            ),
        },
        {
            header: 'Email',
            accessor: 'email',
        },
        {
            header: 'Roles',
            accessor: 'roles',
            render: (u) => (
                <div className="d-flex gap-1 flex-wrap">
                    {(u.roles || []).map((r, idx) => (
                        <span key={idx} className="badge bg-primary-subtle text-primary">
                            {r}
                        </span>
                    ))}
                </div>
            ),
        },
        {
            header: 'Created Date',
            accessor: 'created_at',
        },
    ];

    return (
        <DataTable
            title="System Users & Roles"
            searchPlaceholder="Search users by name, email, role..."
            columns={columns}
            data={users}
            loading={loading}
        />
    );
}
