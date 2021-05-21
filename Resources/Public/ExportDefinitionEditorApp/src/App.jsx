import ExportDefinitionListing from "./components/ExportDefinitionListing";
import ExportDefinitionEditor from "./components/ExportDefinitionEditor";
import FormSelection from "./components/FormSelection";
import { useState } from "react";

const App = ({apiFormData, apiExportDefinition}) => {
  const [step, setStep] = useState('');
  const [formIdentifier, setFormIdentifier] = useState('');
  const [definitionIdentifier, setDefinitionIdentifier] = useState('');
  const [action, setAction] = useState('create')

  const reset = () => {
    setStep('');
    setFormIdentifier('');
    setDefinitionIdentifier('');
  }

  const renderStep = (step) => {
    switch(step) {
      case 'form-selection':
        return (
          <FormSelection setStep={setStep} setFormIdentifier={setFormIdentifier} apiFormData={apiFormData}/>
        )
      case 'export-definition-editor':
        return (
          <ExportDefinitionEditor reset={reset} formIdentifier={formIdentifier} definitionIdentifier={definitionIdentifier} apiFormData={apiFormData} apiExportDefinition={apiExportDefinition} action={action}/>
        )
      default:
          return (
            <ExportDefinitionListing setStep={setStep} setFormIdentifier={setFormIdentifier} setDefinitionIdentifier={setDefinitionIdentifier} apiExportDefinition={apiExportDefinition} setAction={setAction} reset={reset}/>
          )
    }
  }

  return (
    renderStep(step)
  );
}

export default App;
