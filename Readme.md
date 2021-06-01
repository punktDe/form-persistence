# PunktDe.Form.Persistence

Form Persistence Finisher with a backend module to download the form-data.

[![Latest Stable Version](https://poser.pugx.org/punktDe/form-persistence/v/stable)](https://packagist.org/packages/punktDe/form-persistence) [![Total Downloads](https://poser.pugx.org/punktDe/form-persistence/downloads)](https://packagist.org/packages/punktDe/form-persistence) [![License](https://poser.pugx.org/punktDe/form-persistence/license)](https://packagist.org/packages/punktDe/form-persistence)

This package adds a persistence finisher to persist form data into your database. 

It further provides a backend module to download the data in different formats. A n export definition editor let you define your custom export definitions.

Form data is aggregated by the combination of the form identifier and a hash of the form field identifiers.

![Backend Module](Documentation/BackendModule.png)

# Installation
```
composer require punktde/form-persistence
```

After the successful installation run `./flow doctrine:migrate` to initialize the database table.

# Configuration

## Exclude form types from saving

Some form types are only fro structuring the form or to display static text and should not be available for export. These form types can now be excluded using extendable configuration:

	PunktDe:
	  Form:
	    Persistence:
	      finisher:
	        excludedFormTypes:
	          'Neos.Form:StaticText': true

## Export Definitions

Static export definitions can be defined via settings.

**fileNamePattern**: 

Example: `Form-Export-{formIdentifier}-{currentDate}.csv`

The following variables ca be used: 

* formIdentifier
* formVersionHash
* currentDate
* exportDefinitionIdentifier

## Processor Chain

Processing steps for processing the form data are defined in the `processorChain` configuration. This chain is currently used globally for all exports. You can add your own processors using the postionalArraySprtingSyntax for their positionin the chain.

Example: 

	PunktDe:
	  Form:
	    Persistence:
	      processorChain:
	        # My processor
	        flattenArray:
	          class: 'Vendor\FormProcessors\MyProccessor'
	          position: 'end'
	          

# Usage
## Add the SaveFormDataFinisher
### Using the flow form configuration

```
type: 'Neos.Form:Form'
identifier: 'my-form'
renderables:
    ...

finishers:
  saveFormData:
    identifier: 'PunktDe.Form.Persistence:SaveFormDataFinisher'
```

### Using the Neos Form Builder
Require the suggested package neos/form-builder and add the save form data finisher to your node based form in the neos backend.

## Backend Module

### Download form data

A simple backend module is provided to download the form data as CSV. The form version specifies the used fields and their position. 
With that it is taken care, that if the form changes over time, a separate CSV file with consistent headers and column position is generated. 

![Backend Module](Documentation/BackendModule.png)

### Define Export Definitions

The package brings a graphical editor for defining export definitions. With an export definition you can define the fields together whith the field names which are added to the export.

![Backend Module](Documentation/ExportDefinitionEditor.png)

# Developing the package

## Export Definition Editor

### Working with the react app

To start make changes to the export definition app go to the folder `PunktDe.Form.Persistence/Resources/Public/ExportDefinitionEditorApp`
and run the command

```
yarn install
```

After all dependencies are installed, you can adjust the code of the react app. 
The is created with the help of creat-react-app scaffolding tool and therefore uses its build configuration with some adjustments.
To see changes, you need to build the app with the following command.

```
yarn build
```

The generated file `main.js` is located in the folder `build/static/js`.
This file is loaded in the Neos Backend and is the editor you see.
