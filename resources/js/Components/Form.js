import React, {useState} from 'react'
import {
  Grommet, Form, FormField,
  FileInput, Box, Button,
  TextInput, Select, Heading,
  Paragraph
} from 'grommet'
import {Add} from 'grommet-icons'
import {
  isEmpty, get, head,
  map, union, reduce,
  forEach
} from 'lodash'
import axios from 'axios'
import Invoices from './Invoices'
import Total from './Total'

const makeCurrency = (name, value) => `${name}:${value}`

export default () => {
  const [total, setTotal] = useState([])
  const [invoices, setInvoices] = useState([])
  const [invoiceTypes, setInvoiceTypes] = useState([])
  const [currencies, setCurrencies] = useState([])
  const [errors, setErrors] = useState([])

  const submitCurrency = ({value, target}) => {
    const currency = makeCurrency(get(value, 'name'), get(value, 'value'))
    setCurrencies(old => [...old, currency])
    target.reset()
  }

  const onSubmit = ({value, target}) => {
    const formData = new FormData()

    formData.append('csv_file', head(get(value, 'csv_file')))
    forEach(currencies, currency => formData.append('currency[]', currency))
    formData.append('output_currency', get(value, 'output_currency'))

    axios.post('http://localhost:8005/invoice/create', formData)
      .then(res => {
        const {total, invoices, invoiceTypes} = get(res, 'data', {})

        setTotal(total)
        setInvoices(invoices)
        setInvoiceTypes(invoiceTypes)
      })
      .catch(({response}) => {
        setErrors(reduce(
          get(response, 'data.errors'),
          (result, errors) => union(result, errors)
        ))
      })

    target.reset()
  }

  return (
    <Grommet theme={{
      global: {
        font: {
          family: 'Roboto',
          size: '18px',
          height: '20px',
        },
      },
    }}>
      <Box
        width={{default: '100%', max: '500px'}}
        direction="column"
        pad="medium"
      >
        <Form validate="change" onSubmit={submitCurrency}>
          <Heading>Add Currency</Heading>
          <Box direction="row">
            <FormField
              name="name"
              required
              label="Name"
              validate={[
                {regexp: /([A-Z]){3}/}
              ]}
            >
            </FormField>
            <FormField
              name="value"
              required
              label="Value"
              margin={{left: 'medium'}}
              validate={[
                {regexp: /[+-]?([0-9]*[.])?[0-9]+$/}
              ]}
            >
            </FormField>
          </Box>
          <Button type="submit" primary label="Add" icon={<Add/>}/>
        </Form>
        <Form validate="change" onSubmit={onSubmit}>
          <FormField name="output_currency" required label="Output Currency">
            <Select options={currencies} name="output_currency"/>
          </FormField>
          <FormField name="vat_number" label="Vat Number">
            <TextInput name="vat_number"/>
          </FormField>
          <FormField name="csv_file" required label="CSV File">
            <FileInput name="csv_file"/>
          </FormField>
          <Box direction="row" gap="medium">
            <Button type="submit" primary label="Submit"/>
          </Box>
        </Form>
      </Box>
      {!isEmpty(errors) &&
        <Box
          background="status-error"
          margin={{left: 'medium'}}
          width={{default: '100%', max: '500px'}}
          direction="column"
          pad="medium"
          round="medium"
        >
          {map(errors, error => <Paragraph margin="none">{error}</Paragraph>)}
        </Box>}
      {invoices.length > 0 && <Invoices data={invoices} types={invoiceTypes}/>}
      {total.length > 0 && <Total data={total}/>}
    </Grommet>
  )
}
