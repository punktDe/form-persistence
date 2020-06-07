# Form Persistence Finisher and example CSV-Download for Neo.Form

This package adds a persistence finisher to persist form data into your database. 
The saved form data can be downloaded as a csv file in the backend at any given time.

Form data is aggregated by the combination of the form identifier and a hash of the form field identifiers.

## Installation
```
composer require punktde/form-persitence
```

After the successful installation run `./flow doctrine:migrate` to initialize the database table.

# Usage
## Using the flow form configuration

```
type: 'Neos.Form:Form'
identifier: 'my-form'
renderables:
    ...

finishers:
  saveFormData:
    identifier: 'PunktDe.Form.Persistence:SaveFormDataFinisher'
```

## Using the Neos Form Builder
Requires the suggested package neos/form-builder and add the save form data finisher to your node based form in the neos backend.
