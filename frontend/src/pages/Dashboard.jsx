import { useAuth } from '../context/AuthContext';

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

            <div className="bg-white shadow rounded-lg p-6">
                <h2 className="text-xl mb-4">Welcome, {user.firstname}!</h2>
                <p className="mb-4">Role: <span className="font-semibold text-blue-600">{user.role}</span></p>

                {user.role === 'Admin' && (
                    <div className="p-4 bg-gray-100 rounded">
                        <h3 className="font-bold mb-2">Admin Controls</h3>
                        <p>Manage Users, Tours, and Settings here.</p>
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
        </div>
    );
};

export default Dashboard;
