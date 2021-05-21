import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

const appRoot = document.getElementById('app');
const baseUrl = appRoot.getAttribute('data-base-url') || 'https://app.cgm.punkt.dev:8014';

ReactDOM.render(
  <React.StrictMode>
    <App baseUrl={baseUrl}/>
  </React.StrictMode>,
    appRoot
);

