import { useState, useEffect } from "react";

const FormSelection = ({setStep, setFormIdentifier, baseUrl}) => {
    const [list, setList] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [formIdentifier, setSelectedFormIdentifier] = useState('')

    useEffect(() => {
        fetch(baseUrl + '/api/formpersistence/formdata')
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
            {
                isLoading ? <div>Loading...</div> :
                    <div className={'neos-row-fluid'}>
                        <div  className={'neos-control-group'}>
                            <div className={'neos-controls neos-span8'}>
                                <legend>Select a Form</legend>
                                {list.length > 0 ?
                                    <select className={'neos-span9'} onChange={onFormSelected} value={formIdentifier}>
                                        {
                                            list.map(item => {
                                                return <option key={item.id} value={item.id}>{item.label}</option>
                                            })
                                        }
                                    </select>
                                    : <div className={'neos-span12 aCenter'}>Please create an export definition.</div>
                                }
                                <div className={'neos-span2 neos-pull-right'}>
                                    <button className={'neos-button neos-button-primary'} onClick={() => {setFormIdentifier(formIdentifier); setStep('export-definition-editor')}}>Next <i className={'fas fa-chevron-right icon-white'} /></button>
                                </div>
                            </div>
                        </div>
                    </div>
            }
        </>
    )
}

export default FormSelection;
