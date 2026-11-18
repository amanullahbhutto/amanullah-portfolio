import React from 'react';
import ReactDOM from 'react-dom/client';
import AdminApp from './admin/AdminApp';

const adminRoot = document.getElementById('admin-root');

if (adminRoot) {
    const root = ReactDOM.createRoot(adminRoot);
    root.render(
        <React.StrictMode>
            <AdminApp />
        </React.StrictMode>
    );
}
