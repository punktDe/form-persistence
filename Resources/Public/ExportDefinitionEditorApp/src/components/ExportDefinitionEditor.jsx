import React, { useEffect, useState } from 'react';
import update from 'react-addons-update';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';
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
            fetch(apiExportDefinition + '/' + definitionIdentifier),
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
        const line = {
            id: `id-${state.keyStart + 1}`,
            value: state.formFields[0].id,
            conversionValue: ''
        };
        const newState = update(state, {
            lines: {
                $push: [line]
            },
            keyStart: { $set: state.keyStart + 1 }
        });
        setState(newState);
        updateFormSelectOptions(newState)
    };

    const formFieldNameChanged = (event, index) => {
        const newState = update(state, {
            lines: {
                [index]: {
                    value: {
                        $set: event.target.value
                    }
                }
            }
        });
        setState(newState);
        updateFormSelectOptions(newState);
    };

    const conversionFieldNameChanged = (event, index) => {
        setState(update(state, {
            lines: {
                [index]: {
                    conversionValue: {
                        $set: event.target.value
                    }
                }
            }
        }));
    };

    const onDragEnd = (result) => {
        if (!result.destination) {
            return;
        }

        if (result.destination.index === result.source.index) {
            return;
        }

        const lines = reorder(
            state.lines,
            result.source.index,
            result.destination.index
        );

        setState(update(state, {
            lines: {
                $set: lines
            }
        }));
    };

    const removeLine = (index) => {
        const newState = update(state, {
            lines: {
                $splice: [[index, 1]]
            }
        });
        setState(newState);
        updateFormSelectOptions(newState);
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
        setState(update(state, {
            label: {
                $set: event.target.value
            }
        }));
    };

    const onTypeSelected = (event) => {
        setState(update(state, {
            selectedType: {
                $set: event.target.value
            }
        }));
    };

    const onFormSelected = (event) => {
        setSelectedFormIdentifier(event.target.value);
        setList(update(list, {
            $splice: [[0, 1]]
        }))
        state.allFormsData.forEach((formData) => {
            if (formData.__identity === event.target.value) {
                setState(update(state, {
                    formFields: {
                        $set: formData.processedFieldNames.map((item) => {
                            return {
                                id: item,
                                label: item
                            }
                        })
                    }
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
