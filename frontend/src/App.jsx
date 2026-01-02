import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Dashboard from './pages/Dashboard';
import Unauthorized from './pages/Unauthorized';
// Placeholder for Login component
const Login = () => <div className="p-4">Login Page (To be implemented)</div>;

function App() {
    return (
        <Router>
            <AuthProvider>
                <Routes>
                    <Route path="/login" element={<Login />} />
                    <Route path="/unauthorized" element={<Unauthorized />} />

                    {/* Protected Routes */}
                    <Route element={<ProtectedRoute allowedRoles={['Admin', 'User', 'Guide', 'Tourist']} />}>
                        <Route path="/dashboard" element={<Dashboard />} />
                    </Route>

                    {/* Admin Only Route Example */}
                    <Route element={<ProtectedRoute allowedRoles={['Admin']} />}>
                        <Route path="/admin" element={<div>Admin Panel</div>} />
                    </Route>

                    <Route path="/" element={<div className="p-4">Home Page</div>} />
                </Routes>
            </AuthProvider>
        </Router>
    );
}

export default App;
