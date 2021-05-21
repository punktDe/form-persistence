import ExportDefinitionListing from "./components/ExportDefinitionListing";
import ExportDefinitionEditor from "./components/ExportDefinitionEditor";
import FormSelection from "./components/FormSelection";
import { useState } from "react";

const App = ({baseUrl}) => {
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
          <FormSelection setStep={setStep} setFormIdentifier={setFormIdentifier} baseUrl={baseUrl}/>
        )
      case 'export-definition-editor':
        return (
          <ExportDefinitionEditor reset={reset} formIdentifier={formIdentifier} definitionIdentifier={definitionIdentifier} baseUrl={baseUrl} action={action}/>
        )
      default:
          return (
            <ExportDefinitionListing setStep={setStep} setFormIdentifier={setFormIdentifier} setDefinitionIdentifier={setDefinitionIdentifier} baseUrl={baseUrl} setAction={setAction}/>
          )
    }
  }

  return (
    renderStep(step)
  );
}

export default App;
