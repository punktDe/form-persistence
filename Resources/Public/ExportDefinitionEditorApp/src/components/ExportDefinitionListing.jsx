import { useState, useEffect } from "react";

const ExportDefinitionListing = ({ setStep, setFormIdentifier, setDefinitionIdentifier , apiExportDefinition, setAction, reset}) => {
    const [list, setList] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const fetchExportDefinitions = () => {
        setIsLoading(true);
        fetch(apiExportDefinition)
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
    }

    useEffect(() => {
        fetchExportDefinitions()
    }, []);

    const onDelete = (identifier) => {
        setIsLoading(true);

        fetch(apiExportDefinition + '/' + identifier, {
            method: 'DELETE',
        }).then(response => {
            if (response.ok) {
                reset()
                fetchExportDefinitions()
            }
        }).catch(error => {
            console.error('An Error occurred:', error);
            setIsLoading(false);
        })
    }

    return (
        <>
            <div className={'neos-row-fluid'}>
                <div className={'neos-span6'}>
                    <legend>Export definition listing</legend>
                    <table className={'neos-table'}>
                        <thead>
                            <tr>
                                <th>Export definition label</th>
                                <th/>
                            </tr>
                        </thead>
                        <tbody>
                            {
                                isLoading ? <tr>Loading...</tr> :
                                    <>
                                        {list.length > 0 ?
                                            list.map((item) => {
                                                return (
                                                    <tr key={item.id}>
                                                        <td>{item.label}</td>
                                                        <td className={'neos-action'}>
                                                            <div className={'neos-pull-right'}>
                                                                {/* TODO: add edit functionality <button className={'neos-button neos-button-primary'} onClick={() => { setFormIdentifier(item.formIdentifier); setDefinitionIdentifier(item.id); setStep('export-definition-editor'); setAction('update')}}><i className={'fas fa-pencil-alt icon-white'} /> Edit</button> */}
                                                                <button className={'neos-button neos-button-danger'} onClick={(event ) => {onDelete(item.id)}}><i className={'fas fa-trash icon-white'} /> Delete</button>
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
                </div>
            </div>
            <div className={'neos-row-fluid'}>
                <div className={'neos-span4'}>
                    <button onClick={() => {setStep('form-selection')}} className={'neos-button neos-button-primary'}><i className={'fas fa-plus icon-white'} /> create new Definition</button>
                </div>
            </div>
        </>
    )
}

export default ExportDefinitionListing;
