export function isSuitable(fromFields, exportDefinitionFields) {
    return exportDefinitionFields.filter((item) => fromFields.includes(item)).length ===  exportDefinitionFields.length;
}

export function unique (array, propertyName) {
    return array.filter((e, i) => array.findIndex(a => a[propertyName] === e[propertyName]) === i);
}
