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
  forEach, has
} from 'lodash'
import axios from 'axios'
import Invoices from './Invoices'
import Total from './Total'

/**
 * Format currency object
 * @param name
 * @param value
 * @returns {`${string}:${string}`}
 */
const formatCurrency = ({name, value}) => `${name}:${value}`

export default () => {
  const [total, setTotal] = useState([])
  const [invoices, setInvoices] = useState([])
  const [invoiceTypes, setInvoiceTypes] = useState([])
  const [currencies, setCurrencies] = useState([])
  const [errors, setErrors] = useState([])

  /**
   * Submits the currency form and collecting the data into currencies
   * @param value
   * @param target
   */
  const submitCurrency = ({value, target}) => {
    setCurrencies(old => [...old, value])
    target.reset()
  }

  /**
   * Submits
   * @param value
   * @param target
   */
  const onSubmit = ({value, target}) => {
    const formData = new FormData()

    // populate the form data
    formData.append('csv_file', head(get(value, 'csv_file')))
    if (has(value, 'vat_number'))
      formData.append('vat_number', get(value, 'vat_number'))
    forEach(currencies, currency => formData.append('currency[]', formatCurrency(currency)))
    formData.append('output_currency', get(value, 'output_currency.name'))

    // Make api call on form submit
    axios.post('http://localhost:8005/invoice/create', formData)
      .then(res => {
        const {total, invoices, invoiceTypes} = get(res, 'data', {})

        // set document preview data and total calculations
        setTotal(total)
        setInvoices(invoices)
        setInvoiceTypes(invoiceTypes)
      })
      .catch(
        // display server side errors
        ({response}) => setErrors(reduce(
          get(response, 'data.errors'),
          (result, errors) => union(result, errors)
        ))
      )

    // clear form fields on submit
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
        <Form validate="change" required onSubmit={onSubmit}>
          <FormField name="output_currency" label="Output Currency">
            <Select
              options={currencies}
              labelKey={formatCurrency}
              valueKey="name"
              name="output_currency"
            />
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
