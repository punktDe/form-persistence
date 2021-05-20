import React, { useState, useEffect } from "react";
import update from 'react-addons-update';
import { DragDropContext, Droppable } from "react-beautiful-dnd";
import InputLine from "./InputLine";


const getExportDefinitionData = (exportDefnitionIdentifier) => {
    return {
        label: 'SAP',
        types: [
            {
                label: 'CSV',
                value: 'id-csv'
            },
            {
                label: 'EXCEL',
                value: 'id-excel'
            }
        ],
        lines: Array.from({ length: 10 }, (v, k) => k).map(k => {
            const line = {
                id: `id-${k}`,
                value: `value-${k}`,
                conversionValue: ''
            }
            return line;
        }),
        keyStart: 10,
        formFields: [
            {
                id: 'field-1',
                label: 'Product-Id'
            },
            {
                id: 'field-2',
                label: 'Order-Id'
            },
            {
                id: 'field-3',
                label: 'Product-Type'
            },
        ],
        selectedType: ''
    }
}

const reorder = (list, startIndex, endIndex) => {
    const result = Array.from(list);
    const [removed] = result.splice(startIndex, 1);
    result.splice(endIndex, 0, removed);

    return result;
};

const InputLines = ({ lines, formFieldNameChanged, conversionFieldNameChanged, removeLine , formFields}) => {
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
}

const ExportDefinitionEditor = ({ reset, formIdentifer, definitionIdenitifier }) => {

    const initialState = {
        label: '',
        types: [],
        lines: [],
        keyStart: 0,
        formIdentifer: formIdentifer
    }

    const [state, setState] = useState(initialState);

    useEffect(() => {
        setState(getExportDefinitionData(definitionIdenitifier))
    }, []);

    const addLine = () => {
        const line = {
            id: `id-${state.keyStart + 1}`
        }
        setState(update(state, {
            lines: {
                $push: [line]
            },
            keyStart: {$set: state.keyStart + 1 }
        }));
    }

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
    }

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
    }

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
    }

    const removeLine = (index) => {
        setState(update(state, {
            lines: {
                $splice: [[index, 1]]
            }
        }));
    }

    const sendData = () => {
        console.log(JSON.stringify(state));
    }

    const onLabelChanged = (event) => {
        setState(update(state, {
            label: {
                $set: event.target.value
            }
        }));
    }

    const onTypeSelected = (event) => {
        setState(update(state, {
            selectedType: {
                $set: event.target.value
            }
        }))
    }

    return (
        <>
            <div>
                Label <input value={state.label} onChange={onLabelChanged}/>
            </div>
            <div>
                Type <select value={state.selectedType} onChange={onTypeSelected}>
                    {
                        state.types.map(item => {
                            return <option key={item.value} value={item.value}>{item.label}</option>
                        })
                    }
                </select>
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
            <button onClick={addLine}>+</button>
            <button onClick={sendData}>save</button>
            <button onClick={reset}>back</button>
        </>
    );
}

export default ExportDefinitionEditor;