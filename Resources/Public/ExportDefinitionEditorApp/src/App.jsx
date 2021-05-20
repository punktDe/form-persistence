import ExportDefinitionListing from "./components/ExportDefinitionListing";
import ExportDefinitionEditor from "./components/ExportDefinitionEditor";
import FormSelection from "./components/FormSelection";
import { useState } from "react";

const App = () => {
  const [step, setStep] = useState('');
  const [formIdentifier, setFormIdentifier] = useState('');
  const [definitionIdenitifier, setDefinitionIdenitfier] = useState('');

  const reset = () => {
    setStep('');
    setFormIdentifier('');
    setDefinitionIdenitfier('');
  }
  const renderStep = (step) => {
    switch(step) {
      case 'form-selction':
        return (
          <FormSelection setStep={setStep} setFormIdentifier={setFormIdentifier} />
        )
      case 'export-definition-editor':
        return (
          <ExportDefinitionEditor reset={reset} formIdentifier={formIdentifier} definitionIdenitifier={definitionIdenitifier}/>
        )
      default:
          return (
            <ExportDefinitionListing setStep={setStep} setFormIdentifier={setFormIdentifier} setDefinitionIdenitfier={setDefinitionIdenitfier} />
          )
    }
  }

  return (
    renderStep(step)
  );
}

export default App;
