import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

const appRoot = document.getElementById('app');
const apiFormData = appRoot.getAttribute('data-api-formdata');
const apiExportDefinition = appRoot.getAttribute('data-api-exportdefinition');

ReactDOM.render(
  <React.StrictMode>
    <App apiFormData={apiFormData} apiExportDefinition={apiExportDefinition} />
  </React.StrictMode>,
    appRoot
);

