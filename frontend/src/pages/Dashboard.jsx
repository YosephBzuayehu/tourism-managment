import { useAuth } from '../context/AuthContext';
import { useEffect, useState } from 'react';

const Dashboard = () => {
    const { user, logout } = useAuth();

    return (
        <div className="p-8">
            <div className="flex justify-between items-center mb-8">
                <h1 className="text-2xl font-bold">Dashboard</h1>
                <button
                    onClick={logout}
                    className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                >
                    Logout
                </button>
            </div>

            {/* Notification Section */}
            <div className="mb-6 bg-blue-50 p-4 rounded border border-blue-100">
                <h3 className="font-bold text-lg mb-2 text-blue-800">Notifications</h3>
                <NotificationsPanel userId={user.id} />
            </div>

            <h2 className="text-xl mb-4">Welcome, {user.firstname}!</h2>
            <p className="mb-4">Role: <span className="font-semibold text-blue-600">{user.role}</span></p>

            {user.role === 'Admin' && (
                <div className="p-4 bg-gray-100 rounded">
                    <h3 className="font-bold mb-2">Admin Controls</h3>
                    <p className="mb-4">Review and moderate submitted content.</p>

                    <AdminContentPanel />
                </div>
            )}

            {user.role === 'Guide' && (
                <div className="p-4 bg-gray-100 rounded">
                    <h3 className="font-bold mb-2">Guide Portal</h3>
                    <p>View assigned tours and schedules.</p>
                </div>
            )}

            {user.role === 'Tourist' && (
                <div className="p-4 bg-gray-100 rounded">
                    <h3 className="font-bold mb-2">My Trips</h3>
                    <p>Book tours and view history.</p>
                </div>
            )}
        </div>
    );
};

export default Dashboard;

const AdminContentPanel = () => {
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const fetchItems = async () => {
        setLoading(true);
        setError(null);
        try {
            const res = await fetch('/auth-system/get_content.php', { credentials: 'include' });
            const json = await res.json();
            if (json.status === 'success') setItems(json.data);
            else setError(json.message || 'Failed to load');
        } catch (e) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchItems();
    }, []);

    const updateStatus = async (id, action) => {
        try {
            const res = await fetch('/auth-system/update_status.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, action })
            });
            const json = await res.json();
            if (json.status === 'success') {
                fetchItems();
            } else {
                alert(json.message || 'Failed');
            }
        } catch (e) {
            alert(e.message);
        }
    };

    return (
        <div>
            {loading && <p>Loading content...</p>}
            {error && <p className="text-red-600">{error}</p>}
            {!loading && !error && (
                <div className="space-y-2">
                    {items.length === 0 && <p>No content to moderate.</p>}
                    {items.map(item => (
                        <div key={item.id} className="p-3 bg-white rounded shadow">
                            <div className="flex justify-between items-start">
                                <div>
                                    <h4 className="font-semibold">{item.title}</h4>
                                    <p className="text-sm text-gray-600">By {item.firstname} {item.lastname} — {new Date(item.created_at).toLocaleString()}</p>
                                    <p className="mt-2">{item.body.slice(0, 200)}{item.body.length > 200 ? "..." : ""}</p>
                                </div>
                                <div className="ml-4 text-right">
                                    <p className="mb-2">Status: <span className="font-semibold">{item.status}</span></p>
                                    <button disabled={item.status === 'approved'} onClick={() => updateStatus(item.id, 'approve')} className="bg-green-500 text-white px-3 py-1 rounded mr-2 hover:bg-green-600 disabled:opacity-50">Approve</button>
                                    <button disabled={item.status === 'rejected'} onClick={() => updateStatus(item.id, 'reject')} className="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 disabled:opacity-50">Reject</button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

const NotificationsPanel = ({ userId }) => {
    const [notifs, setNotifs] = useState([]);

    useEffect(() => {
        if (!userId) return;
        fetch(`http://localhost:8000/auth-system/get_notifications.php?user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                if (Array.isArray(data)) setNotifs(data);
            })
            .catch(err => console.error(err));
    }, [userId]);

    if (notifs.length === 0) return <p className="text-gray-500 italic">No new notifications.</p>;

    return (
        <ul className="space-y-2">
            {notifs.map(n => (
                <li key={n.id} className={`p-2 rounded ${n.is_read ? 'bg-gray-50' : 'bg-white shadow-sm border-l-4 border-blue-500'}`}>
                    <p className="text-sm">{n.message}</p>
                    <span className="text-xs text-gray-500">{new Date(n.created_at).toLocaleString()}</span>
                </li>
            ))}
        </ul>
    );
};
