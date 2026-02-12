import React from 'react';
import ReactDOM from 'react-dom/client';
import { registerSW } from 'virtual:pwa-register';
import App from './App';
import './styles.css';

class ErrorBoundary extends React.Component<React.PropsWithChildren, { hasError: boolean; message: string }> {
  constructor(props: React.PropsWithChildren) {
    super(props);
    this.state = { hasError: false, message: '' };
  }

  static getDerivedStateFromError(error: Error) {
    return { hasError: true, message: error.message || 'Unexpected UI error.' };
  }

  override componentDidCatch(error: Error) {
    console.error('UI crash:', error);
  }

  override render() {
    if (this.state.hasError) {
      return (
        <main style={{ padding: '1rem', fontFamily: 'Inter, Arial, sans-serif' }}>
          <h1>Pharmacy Medicine Locator</h1>
          <p>Something went wrong while loading the app UI.</p>
          <p><strong>Error:</strong> {this.state.message}</p>
          <p>Try a hard refresh (Ctrl+F5) or clear site data if this happened after an update.</p>
        </main>
      );
    }

    return this.props.children;
  }
}

if (import.meta.env.DEV && 'serviceWorker' in navigator) {
  void navigator.serviceWorker.getRegistrations().then((registrations) => {
    registrations.forEach((registration) => {
      void registration.unregister();
    });
  });
}

if (import.meta.env.PROD) {
  registerSW({
    immediate: true,
    onRegisterError(error) {
      console.error('Service worker registration failed:', error);
    }
  });
}

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>
);
