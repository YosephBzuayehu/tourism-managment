import { useState } from "react";
import "./Login.css";

export default function Login() {
  const [identifier, setIdentifier] = useState("");
  const [password, setPassword] = useState("");
  const [errors, setErrors] = useState({});

  const handleSubmit = (e) => {
    e.preventDefault();

    let temp = {};
    if (!identifier) temp.identifier = "Email or username required";
    if (!password || password.length < 6)
      temp.password = "Password must be at least 6 characters";

    setErrors(temp);

    if (Object.keys(temp).length === 0) {
      alert("Login successful");
      setIdentifier("");
      setPassword("");
    }
  };

  return (
    <div className="login-container">
      <form className="login-card" onSubmit={handleSubmit}>
        <h2>Welcome Back</h2>

        <div className="field">
          <label>Email or Username</label>
          <input
            type="text"
            value={identifier}
            onChange={(e) => setIdentifier(e.target.value)}
            placeholder="Enter email or username"
          />
          {errors.identifier && (
            <span className="error">{errors.identifier}</span>
          )}
        </div>

        <div className="field">
          <label>Password</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Enter password"
          />
          {errors.password && (
            <span className="error">{errors.password}</span>
          )}
        </div>

        <button type="submit" className="btn">Login</button>

        <p className="redirect">
          Don’t have an account? <a href="/register">Register</a>
        </p>
      </form>
    </div>
  );
}
