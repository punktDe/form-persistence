import ExportDefinitionListing from "./components/ExportDefinitionListing";
import ExportDefinitionEditor from "./components/ExportDefinitionEditor";
import { useState } from "react";

const App = ({apiFormData, apiExportDefinition}) => {
  const [step, setStep] = useState('');
  const [definitionIdentifier, setDefinitionIdentifier] = useState('');
  const [action, setAction] = useState('create')

  const reset = () => {
    setStep('');
    setDefinitionIdentifier('');
    setAction('create');
  }

  const renderStep = (step) => {
    switch(step) {
      case 'export-definition-editor':
        return (
          <ExportDefinitionEditor reset={reset} definitionIdentifier={definitionIdentifier} apiFormData={apiFormData} apiExportDefinition={apiExportDefinition} action={action}/>
        )
      default:
        return (
          <ExportDefinitionListing setStep={setStep} setDefinitionIdentifier={setDefinitionIdentifier} apiFormData={apiFormData} apiExportDefinition={apiExportDefinition} setAction={setAction} reset={reset}/>
        )
    }
  }

  return (
    renderStep(step)
  );
}

export default App;
