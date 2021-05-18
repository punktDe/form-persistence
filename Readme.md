# PunktDe.Form.Persistence

Form Persistence Finisher with a backend module to download the form-data.

[![Latest Stable Version](https://poser.pugx.org/punktDe/form-persistence/v/stable)](https://packagist.org/packages/punktDe/form-persistence) [![Total Downloads](https://poser.pugx.org/punktDe/form-persistence/downloads)](https://packagist.org/packages/punktDe/form-persistence) [![License](https://poser.pugx.org/punktDe/form-persistence/license)](https://packagist.org/packages/punktDe/form-persistence)

This package adds a persistence finisher to persist form data into your database. 
The saved form data can be downloaded as a csv file in the backend at any given time.

Form data is aggregated by the combination of the form identifier and a hash of the form field identifiers.

## Installation
```
composer require punktde/form-persitence
```

After the successful installation run `./flow doctrine:migrate` to initialize the database table.

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

## Download the data using the backend module

A simple backend module is provided to download the form data as CSV. The form version specifies the used fields and their position. 
With that it is taken care, that if the form changes over time, a separate CSV file with consistent headers and column position is generated. 

![Example](Documentation/BackendModule.png)

