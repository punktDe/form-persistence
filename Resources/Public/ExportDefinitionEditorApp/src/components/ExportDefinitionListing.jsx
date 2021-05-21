import { useState, useEffect } from "react";
import update from 'react-addons-update';

const ExportDefinitionListing = ({ setStep, setFormIdentifier, setDefinitionIdentifier , baseUrl, setAction}) => {
    const [list, setList] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    useEffect(() => {
        fetch(baseUrl + '/api/formpersistence/exportdefinition')
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw response
                }
            }).then(data => {
                const list = data.map((item) => {
                    return {
                      id: item.__identity,
                      formIdentifier: '',
                      label: item.label
                    }
                });
                setList(list);
            }).catch(error => {
                console.error('An Error occurred:', error);
            }).finally(() => {
                setIsLoading(false);
            })

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
            <table className={'neos-table'}>
                <thead>
                    <tr>
                        <th>Export Definition Label</th>
                        <th/>
                    </tr>
                </thead>
                <tbody>
                    {
                        isLoading ? <tr>Loading...</tr> :
                            <>
                                {list.length > 0 ?
                                    list.map((item, index) => {
                                        return (
                                            <tr key={item.id}>
                                                <td>{item.label}</td>
                                                <td className={'neso-action'}>
                                                    <div className={'neos-pull-right'}>
                                                        <button className={'neos-button neos-button-primary'} onClick={() => { setFormIdentifier(item.formIdentifier); setDefinitionIdentifier(item.id); setStep('export-definition-editor'); setAction('update')}}><i className={'fas fa-pencil-alt icon-white'} /> Edit</button>
                                                        <button className={'neos-button neos-button-danger'} onClick={() => {onDelete(index)}}><i className={'fas fa-trash icon-white'} /> Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        )
                                    })
                                    : <div>Please create an export definition.</div>
                                }
                            </>
                    }
                </tbody>
            </table>
            <button onClick={() => {setStep('form-selection')}} className={'neos-button neos-button-primary'}><i className={'fas fa-plus icon-white'} /> create new Definition</button>
        </>
    )
}

export default ExportDefinitionListing;
