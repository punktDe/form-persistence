import { useState, useEffect } from "react";
import update from 'react-addons-update';

const ExportDefinitionListing = ({ setStep, setFormIdentifier, setDefinitionIdenitfier }) => {
    const [list, setList] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const data = [
            {
                id: '1',
                label: 'SAP CSV',
                formIdentifer: '1',
            },
            {
                id: '2',
                label: 'Generic Form CSV',
                formIdentifer: '2',
            },
        ]

        const id = setTimeout(() => {
            setList(data);
            setIsLoading(false);
            clearTimeout(id);
        }, 1000)
    }, []);

    const onDelete = (index) => {
        setIsLoading(true);

        const id = setTimeout(() => {
            setList(update(list, {
                $splice: [[index, 1]]
            }))
            setIsLoading(false);
            clearTimeout(id);
        }, 1000)
    }

    return (
        <>
            {
                isLoading ? <div>Loading...</div> :
                    <>
                        <div>
                            {list.length > 0 ?
                                list.map((item, index) => {
                                    return (
                                        <div key={item.id}>{item.label}
                                            <button onClick={() => { setFormIdentifier(item.formIdentifer); setDefinitionIdenitfier(item.id); setStep('export-definition-editor'); }}>Edit</button>
                                            <button onClick={() => {onDelete(index)}}>Delete</button>
                                        </div>
                                    )
                                })
                                : <div>Please create an export definition.</div>
                            }
                        </div>
                        <div>
                            <button onClick={() => {setStep('form-selction')}}>create new Defenition</button>
                        </div>
                    </>
            }
        </>
    )
}

export default ExportDefinitionListing;