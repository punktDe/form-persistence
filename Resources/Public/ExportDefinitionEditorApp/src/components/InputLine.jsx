import { Draggable } from "react-beautiful-dnd";

const InputLine = ({ inputLine, index, formFields, formFieldNameChanged, conversionFieldNameChanged , removeLine}) => {
    return (
        <Draggable draggableId={inputLine.id} index={index}>
            {provided => (
                <div
                    className="inputs-line"
                    ref={provided.innerRef}
                    {...provided.draggableProps}
                    {...provided.dragHandleProps}
                >
                    <select onChange={(event) => { formFieldNameChanged(event, index) }} value={inputLine.valu}>
                        {
                            formFields.map(item => {
                                return <option key={item.id} value={item.id}>{item.label}</option>
                            })
                        }
                    </select>
                    <input
                        type="text"
                        placeholder="conversion to field name"
                        value={inputLine.conversionValue}
                        onChange={(event) => { conversionFieldNameChanged(event, index) }}
                    />
                    <button onClick={() => { removeLine(index) }}>-</button>
                </div>
            )}
        </Draggable>
    )
}

export default InputLine;