import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

const appRoot = document.getElementById('app');
const apiFormData = appRoot.getAttribute('data-api-formdata');
const apiExportDefinition = appRoot.getAttribute('data-api-exportdefinition');

const root = createRoot(appRoot);
root.render(
  <StrictMode>
    <App apiFormData={apiFormData} apiExportDefinition={apiExportDefinition} />
  </StrictMode>
);
