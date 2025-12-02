import React, { useEffect, useState } from 'react';
import { DragDropContext, Droppable } from '@hello-pangea/dnd';
import InputLine from './InputLine';
import { generateFormFieldsForExportDefinition, isSuitable, uniqueForProperty } from '../utility/Helper';

const reorder = (list, startIndex, endIndex) => {
    const result = Array.from(list);
    const [removed] = result.splice(startIndex, 1);
    result.splice(endIndex, 0, removed);

    return result;
};

const InputLines = ({ lines, formFieldNameChanged, conversionFieldNameChanged, removeLine, formFields }) => {
    return lines.map((line, index) => (
        <InputLine
            inputLine={line}
            index={index}
            key={line.id}
            formFields={formFields}
            formFieldNameChanged={formFieldNameChanged}
            conversionFieldNameChanged={conversionFieldNameChanged}
            removeLine={removeLine}
        />
    ));
};

const ExportDefinitionEditor = ({ reset, definitionIdentifier, apiFormData, apiExportDefinition, action }) => {

    const initialState = {
        label: '',
        types: [],
        lines: [],
        keyStart: 0,
        allFormsData : []
    };

    const [state, setState] = useState(initialState);
    const [isLoading, setIsLoading] = useState(true);
    const [list, setList] = useState([]);
    const [formIdentifier, setSelectedFormIdentifier] = useState('')
    const fetchData = () => {
        Promise.all([
            fetch(apiFormData),
            fetch(`${apiExportDefinition}${definitionIdentifier ? `/${definitionIdentifier}` : ''}`),
            fetch(apiExportDefinition)
        ]).then(([formsDataResponse, exportDefinitionResponse, allExportDefinitionResponse]) => {
            Promise.all([
                formsDataResponse.json(),
                exportDefinitionResponse.json(),
                allExportDefinitionResponse.json()
            ]).then(([formsData, exportDefinitionData, allExportDefinitionsData]) => {
                const exportDefinitionFields = definitionIdentifier ? Object.keys(exportDefinitionData.definition) : [];
                const uniqueFormFields = generateFormFieldsForExportDefinition(formsData, exportDefinitionFields);
                let list = [];
                if (definitionIdentifier) {
                    let finished = false;
                    formsData.forEach((formData) => {
                        if (finished) {
                            return;
                        }
                        if (isSuitable(formData.processedFieldNames, exportDefinitionFields)) {
                            finished = true;
                            setSelectedFormIdentifier(formData.__identity)
                        }
                    })
                    list = formsData.map((item) => {
                        if (isSuitable(item.processedFieldNames, exportDefinitionFields)) {
                            return {
                                id: item.__identity,
                                label: item.formIdentifier + '-' + item.hash.substring(0, 10)
                            }
                        }
                    }).filter((item) => item !== undefined);
                } else {
                    list = formsData.map((item) => {
                        return {
                            id: item.__identity,
                            label: item.formIdentifier + '-' + item.hash.substring(0, 10)
                        }
                    });
                    list.unshift({id: '', label: 'Please select a from'});
                }
                setList(list);
                const data = {
                    label: exportDefinitionData.label || '',
                    types: allExportDefinitionsData.length > 0 ? uniqueForProperty(allExportDefinitionsData.map((item) => {
                        return {
                            label: item.exporter,
                            value: item.exporter
                        };
                    }), 'label') : [{ label: 'csv', value: 'csv' }, { label: 'excel', value: 'excel' }],
                    lines: Object.keys(exportDefinitionData?.definition || {}).map((item, index) => {
                        return {
                            id: `id-${index}`,
                            value: item,
                            conversionValue: exportDefinitionData.definition[item].changeKey
                        };
                    }),
                    keyStart: Object.keys(exportDefinitionData?.definition || {}).length || 0,
                    formFields: uniqueFormFields.map((item) => {
                        return {
                            id: item,
                            label: item
                        };
                    }),
                    selectedType: exportDefinitionData?.exporter || allExportDefinitionsData[0]?.exporter || 'csv',
                    allFormsData: formsData
                };
                setState(data);
            }).catch(error => {
                console.error('An Error occurred:', error);
            })
        }).catch(error => {
            console.error('An Error occurred:', error);
        }).finally(() => {
            setIsLoading(false);
        });
    }

    useEffect(() => {
        fetchData();
    }, []);

    const addLine = () => {
        setState(prevState => {
            const line = {
                id: `id-${prevState.keyStart + 1}`,
                value: prevState.formFields[0].id,
                conversionValue: ''
            };
            const newState = {
                ...prevState,
                lines: [...prevState.lines, line],
                keyStart: prevState.keyStart + 1
            };
            updateFormSelectOptions(newState);
            return newState;
        });
    };

    const formFieldNameChanged = (event, index) => {
        setState(prevState => {
            const newLines = [...prevState.lines];
            newLines[index] = {
                ...newLines[index],
                value: event.target.value
            };
            const newState = {
                ...prevState,
                lines: newLines
            };
            updateFormSelectOptions(newState);
            return newState;
        });
    };

    const conversionFieldNameChanged = (event, index) => {
        setState(prevState => {
            const newLines = [...prevState.lines];
            newLines[index] = {
                ...newLines[index],
                conversionValue: event.target.value
            };
            return {
                ...prevState,
                lines: newLines
            };
        });
    };

    const onDragEnd = (result) => {
        if (!result.destination) {
            return;
        }

        if (result.destination.index === result.source.index) {
            return;
        }

        setState(prevState => {
            const lines = reorder(
                prevState.lines,
                result.source.index,
                result.destination.index
            );
            return {
                ...prevState,
                lines: lines
            };
        });
    };

    const removeLine = (index) => {
        setState(prevState => {
            const newLines = [...prevState.lines];
            newLines.splice(index, 1);
            const newState = {
                ...prevState,
                lines: newLines
            };
            updateFormSelectOptions(newState);
            return newState;
        });
    };

    const sendData = () => {
        setIsLoading(true);

        const data = {
            label: state.label,
            exporter: state.selectedType,
            definition: Object.assign({}, ...state.lines.map((line) => {
                return {
                    [line.value]: {
                        changeKey: line.conversionValue
                    }
                };
            }))
        };
        if (action === 'create') {
            fetch(apiExportDefinition, {
                headers: {
                    'Content-type': 'application/json; charset=UTF-8'
                },
                method: 'POST',
                body: JSON.stringify(data)
            }).then(response => {
                if (!response.ok) {
                    throw response;
                }
                reset()
            }).catch(error => {
                console.error('An Error occurred:', error);
                setIsLoading(false);
            });
        } else {
            fetch(apiExportDefinition + '/' + definitionIdentifier, {
                headers: {
                    'Content-type': 'application/json; charset=UTF-8'
                },
                method: 'PUT',
                body: JSON.stringify(data)
            }).then(response => {
                if (!response.ok) {
                    throw response;
                }
                fetchData();
            }).catch(error => {
                console.error('An Error occurred:', error);
                setIsLoading(false);
            })
        }
    };

    const onLabelChanged = (event) => {
        setState(prevState => ({
            ...prevState,
            label: event.target.value
        }));
    };

    const onTypeSelected = (event) => {
        setState(prevState => ({
            ...prevState,
            selectedType: event.target.value
        }));
    };

    const onFormSelected = (event) => {
        setSelectedFormIdentifier(event.target.value);
        setList(prevList => {
            const newList = [...prevList];
            newList.splice(0, 1);
            return newList;
        });
        state.allFormsData.forEach((formData) => {
            if (formData.__identity === event.target.value) {
                setState(prevState => ({
                    ...prevState,
                    formFields: formData.processedFieldNames.map((item) => {
                        return {
                            id: item,
                            label: item
                        }
                    })
                }));
            }
        });
    }

    const updateFormSelectOptions = (state) => {
        const currentDefinitionFields = state.lines.map((line) => {
            return line.value
        });
        const list = state.allFormsData.map((item) => {
            if (isSuitable(item.processedFieldNames, currentDefinitionFields)) {
                return {
                    id: item.__identity,
                    label: item.formIdentifier + '-' + item.hash.substring(0, 10)
                }
            }
        }).filter((item) => item !== undefined);

        setList(list)
    }

    return (
        <>
            <div className={'neos-row-fluid'}>
                <div className={'neos-span8 neos-table'}>
                    <legend>Export Definition</legend>
                    {isLoading ? <div>Loading...</div> :
                        <>
                            <div className={'neos-control-group'}>
                                <label className={'neos-control-label'} htmlFor={'id-label'}>
                                    Label
                                </label>
                                <div className={'neos-controls'}>
                                    <input className={'neos-span12'} value={state.label} onChange={onLabelChanged} id={'id-label'} type={'text'}/>
                                </div>
                            </div>
                            <div className={'neos-control-group'}>
                                <label className={'neos-control-label'} htmlFor={'id-type'}>
                                    Type
                                </label>
                                <div className={'neos-controls'}>
                                     <select className={'neos-span12'} value={state.selectedType} onChange={onTypeSelected} id={'id-type'}>
                                        {
                                            state.types.map(item => {
                                                return <option key={item.value} value={item.value}>{item.label}</option>;
                                            })
                                        }
                                    </select>
                                </div>
                            </div>
                            <div className={'neos-control-group'}>
                                <label className={'neos-control-label'} htmlFor={'form-selection'}>Select a form</label>
                                <div className={'neos-controls'}>
                                    <select className={'neos-span12'} onChange={onFormSelected} value={formIdentifier} id={'form-selection'}>
                                        {
                                            list.map(item => {
                                                return <option key={item.id} value={item.id}>{item.label}</option>
                                            })
                                        }
                                    </select>
                                </div>
                            </div>
                            { state.lines.length > 0 ?
                                <>
                                    <div className="neos-control-group">
                                        <div style={{ paddingLeft: '50px', width: '40%', fontSize: '18px', display: 'inline-block', height: '32px', marginTop: '16px' }}>Form field name</div>
                                        <div style={{ paddingLeft: '48px', width: '40%', fontSize: '18px', display: 'inline-block', height: '32px', marginTop: '16px' }}>Exported field name</div>
                                    </div>
                                    <DragDropContext onDragEnd={onDragEnd}>
                                        <Droppable droppableId="assosiative-fields">
                                            {provided => (
                                                <div ref={provided.innerRef} {...provided.droppableProps}>
                                                    <InputLines
                                                        lines={state.lines}
                                                        formFields={state.formFields}
                                                        formFieldNameChanged={formFieldNameChanged}
                                                        conversionFieldNameChanged={conversionFieldNameChanged}
                                                        removeLine={removeLine}
                                                    />
                                                    {provided.placeholder}
                                                </div>
                                            )}
                                        </Droppable>
                                    </DragDropContext>
                                </>
                                : formIdentifier !== '' ? 'Please add a field association for the form to define the export definition' : ''
                            }
                            { formIdentifier !== '' ?
                                <div className={'neos-pull-right'}>
                                    <button className={'neos-button neos-button-primary'} onClick={addLine}><i className={'fas fa-plus icon-white'}/> Add a field association</button>
                                </div>
                                : ''
                            }
                        </>
                    }
                </div>
            </div>
            <div className={'neos-row-fluid'}>
                <div className={'neos-span5'}>
                    <button className={'neos-button neos-button-primary'} onClick={reset}><i className={'fas fa-chevron-left icon-white'}/> Back to export definition listing</button>
                    { formIdentifier !== '' ?
                        <button style={{marginLeft: '16px' }} className={'neos-button neos-button-primary'} onClick={sendData}><i className={'fas fa-save icon-white'}/> Save export definition</button>
                        : ''
                    }
                </div>
            </div>
        </>
    );
}

export default ExportDefinitionEditor;
