import { useState, useEffect } from "react";

const FormSelection = ({setStep, setFormIdentifier, apiFormData}) => {
    const [list, setList] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [formIdentifier, setSelectedFormIdentifier] = useState('')

    useEffect(() => {
        fetch(apiFormData)
            .then(response => {
                if(response.ok) {
                    return response.json();
                } else {
                    throw response
                }
            }).then(data => {
                const list = data.map((item) => {
                    return {
                        id: item.__identity,
                        label: item.formIdentifier
                    }
                })
                setList(list)
                setSelectedFormIdentifier(data[0].__identity)
            }).catch(error => {
                console.error('An Error occurred:', error);
            }).finally(() => {
                setIsLoading(false);
            })
    }, []);

    const onFormSelected = (event) => {
        setSelectedFormIdentifier(event.target.value);
    }

    return (
        <>
            <div className={'neos-row-fluid'}>
                <div className={'neos-span6'}>
                    <legend>From selection for export definition</legend>
                    {
                        isLoading ? <div>Loading...</div> :
                            <div className={'neos-control-group'}>
                                <label className={'neos-control-label'} htmlFor={'form-selection'}>Select a form</label>
                                <div className={'neos-controls'}>
                                    {list.length > 0 ?
                                        <select className={'neos-span12'} onChange={onFormSelected} value={formIdentifier} id={'form-selection'}>
                                            {
                                                list.map(item => {
                                                    return <option key={item.id} value={item.id}>{item.label}</option>
                                                })
                                            }
                                        </select>
                                        : <div className={'neos-span12 aCenter'}>Please create an export definition.</div>
                                    }
                                </div>
                            </div>
                    }
                </div>
            </div>
            <div className={'neos-row-fluid'}>
                <div className={'neos-span4'}>
                    <button className={'neos-button neos-button-primary'} onClick={() => {setFormIdentifier(formIdentifier); setStep('export-definition-editor')}}>Configure export definition for form <i className={'fas fa-chevron-right icon-white'} /></button>
                </div>
            </div>
        </>
    )
}

export default FormSelection;
