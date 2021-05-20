import { useState, useEffect } from "react";

const FormSelection = ({setStep, setFormIdentifier}) => {
    const [list, setList] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [formIdentifier, setSelectedFormIdentiier] = useState('')

    useEffect(() => {
        const data = [
            {
                id: '1',
                label: 'Form 1',
            },
            {
                id: '2',
                label: 'Form 2',
            },
        ]

        const id = setTimeout(() => {
            setList(data);
            setSelectedFormIdentiier(data[0].id);
            setIsLoading(false);
            clearTimeout(id);
        }, 1000)
    }, []);

    const onFormSelected = (event) => {
        setSelectedFormIdentiier(event.target.value);
    }

    return (
        <>
            {
                isLoading ? <div>Loading...</div> :
                    <>
                        <div>
                            {list.length > 0 ?
                                <select onChange={onFormSelected} value={formIdentifier}>
                                    {list.map(item => {
                                        return <option key={item.id} value={item.id}>{item.label}</option>
                                    })
                                    }
                                </select>
                                : <div>Please create an export definition.</div>
                            }
                        </div>
                        <div>
                        <button onClick={() => {setFormIdentifier(formIdentifier); setStep('export-definition-editor')}}>next</button>
                        </div>
                    </>
            }
        </>
    )
}

export default FormSelection;