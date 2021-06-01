import flatten from 'arr-flatten';
import unique  from 'array-unique'

export function isSuitable(fromFields, exportDefinitionFields) {
    return exportDefinitionFields.filter((item) => fromFields.includes(item)).length ===  exportDefinitionFields.length;
}

export function uniqueForProperty (array, propertyName) {
    return array.filter((e, i) => array.findIndex(a => a[propertyName] === e[propertyName]) === i);
}

export function generateFormFieldsForExportDefinition (formsData, exportDefinitionFields = []) {
    if (exportDefinitionFields.length === 0 ) {
        return unique(flatten(formsData.map((formData) => {
            return formData.processedFieldNames
        })));
    }

    return unique(flatten(formsData.map((formData) => {
        if (isSuitable(formData.processedFieldNames, exportDefinitionFields)) {
            return formData.processedFieldNames
        }
    }))).filter((item) => item !== undefined);
}
