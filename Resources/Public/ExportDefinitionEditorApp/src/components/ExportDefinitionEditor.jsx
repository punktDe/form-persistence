import React, { useEffect, useState } from 'react';
import update from 'react-addons-update';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';
import InputLine from './InputLine';

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

const unique = (array, propertyName) => {
    return array.filter((e, i) => array.findIndex(a => a[propertyName] === e[propertyName]) === i);
}

const ExportDefinitionEditor = ({ reset, formIdentifier, definitionIdentifier, apiFormData, apiExportDefinition, action }) => {

    const initialState = {
        label: '',
        types: [],
        lines: [],
        keyStart: 0,
        formIdentifier: formIdentifier
    };

    const [state, setState] = useState(initialState);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        Promise.all([
            fetch(apiFormData + '/' + formIdentifier),
            fetch(apiExportDefinition + '/' + definitionIdentifier),
            fetch(apiExportDefinition)
        ]).then(([formDataResponse, exportDefinitionResponse, allExportDefinitionResponse]) =>
            Promise.all([
                formDataResponse.json(),
                exportDefinitionResponse.json(),
                allExportDefinitionResponse.json()
            ]).then(([formData, exportDefinitionData, allExportDefinitionResponseData]) => {
                const data = {
                    label: exportDefinitionData.label || '',
                    types: unique(allExportDefinitionResponseData.map((item) => {
                        return {
                            label: item.exporter,
                            value: item.exporter
                        };
                    }), 'label'),
                    lines: Object.keys(exportDefinitionData?.definition || {}).map((item, index) => {
                        return {
                            id: `id-${index}`,
                            value: item,
                            conversionValue: ''
                        };
                    }),
                    keyStart: formData?.processedFormData.length || 0,
                    formFields: Object.keys(formData.processedFormData).map((item) => {
                        return {
                            id: item,
                            label: item
                        };
                    }),
                    selectedType: exportDefinitionData?.exporter || allExportDefinitionResponseData[0].exporter
                };
                setState(data);
            })
        ).catch(error => {
            console.error('An Error occurred:', error);
        }).finally(() => {
            setIsLoading(false);
        });
    }, []);

    const addLine = () => {
        const line = {
            id: `id-${state.keyStart + 1}`
        };
        setState(update(state, {
            lines: {
                $push: [line]
            },
            keyStart: { $set: state.keyStart + 1 }
        }));
    };

    const formFieldNameChanged = (event, index) => {
        setState(update(state, {
            lines: {
                [index]: {
                    value: {
                        $set: event.target.value
                    }
                }
            }
        }));
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
        setState(update(state, {
            lines: {
                $splice: [[index, 1]]
            }
        }));
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
                reset();
            }).catch(error => {
                console.error('An Error occurred:', error);
            }).finally(() => {
                setIsLoading(false);
            })
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
                reset();
            });
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
                            <DragDropContext onDragEnd={onDragEnd}>
                                <Droppable droppableId="assosittive-fields">
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
                            <div className={'neos-pull-right'}>
                                <button className={'neos-button neos-button-primary'} onClick={addLine}><i className={'fas fa-plus icon-white'}/> Add a field association</button>
                                <button className={'neos-button neos-button-primary'} onClick={sendData}><i className={'fas fa-save icon-white'}/> Save export definition</button>
                            </div>
                        </>
                    }
                </div>
            </div>
            <div className={'neos-row-fluid'}>
                <div className={'neos-span4'}>
                    <button className={'neos-button neos-button-primary'} onClick={reset}><i className={'fas fa-chevron-left icon-white'}/> Back to export definition listing</button>
                </div>
            </div>
        </>
    );
}

export default ExportDefinitionEditor;
