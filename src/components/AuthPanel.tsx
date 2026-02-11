import { type FormEvent, useState } from 'react';
import { login, logout, register } from '../services/authService';

interface AuthPanelProps {
  email?: string | null;
}

export function AuthPanel({ email }: AuthPanelProps) {
  const [isRegisterMode, setIsRegisterMode] = useState(false);
  const [formEmail, setFormEmail] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setError(null);

    try {
      if (isRegisterMode) {
        await register(formEmail, password);
      } else {
        await login(formEmail, password);
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Authentication failed');
    } finally {
      setBusy(false);
    }
  }

  if (email) {
    return (
      <section className="panel">
        <h2>Account</h2>
        <p>Signed in as: {email}</p>
        <button onClick={() => void logout()} className="secondary">
          Sign out
        </button>
      </section>
    );
  }

  return (
    <section className="panel">
      <h2>{isRegisterMode ? 'Create account' : 'Sign in'}</h2>
      <form onSubmit={handleSubmit} className="grid">
        <label>
          Email
          <input type="email" value={formEmail} onChange={(event) => setFormEmail(event.target.value)} required />
        </label>
        <label>
          Password
          <input
            type="password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            minLength={6}
            required
          />
        </label>
        <div className="button-row">
          <button type="submit" disabled={busy}>
            {busy ? 'Please wait...' : isRegisterMode ? 'Create account' : 'Sign in'}
          </button>
          <button type="button" className="secondary" onClick={() => setIsRegisterMode((current) => !current)}>
            {isRegisterMode ? 'Have an account? Sign in' : 'New user? Create account'}
          </button>
        </div>
      </form>
      {error && <p className="error">{error}</p>}
    </section>
  );
}
