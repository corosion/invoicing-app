import React, {useState} from 'react'
import {
  Grommet, Form, FormField,
  FileInput, Box, Button,
  DataTable, Text, Heading
} from 'grommet'
import {get, head} from 'lodash'
import axios from 'axios'

export default () => {
  const [total, setTotal] = useState([])
  const [invoices, setInvoices] = useState([])
  const [invoiceTypes, setInvoiceTypes] = useState([])

  const onSubmit = ({value, target}) => {
    const formData = new FormData()

    formData.append('csv_file', head(get(value, 'csv_file')));
    formData.append('currency[]', 'EUR:1')
    formData.append('currency[]', 'USD:0.98')
    formData.append('currency[]', 'GBP:1.25')
    formData.append('output_currency', 'EUR')

    axios.post('http://localhost:8005/invoice/create', formData)
    .then(res => {
      const { total, invoices, invoiceTypes } = get(res, 'data', {})

      setTotal(total)
      setInvoices(invoices)
      setInvoiceTypes(invoiceTypes)
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
        <Form validate="change" onSubmit={onSubmit}>
          <FormField name="csv_file" required>
            <FileInput name="csv_file"/>
          </FormField>
          <Box direction="row" gap="medium">
            <Button type="submit" primary label="Submit"/>
          </Box>
        </Form>
      </Box>
      {
        invoices.length > 0 &&
        <Box
          width={{default: '100%', max: '800px'}}
          direction="column"
          pad="medium"
        >
          <Heading level={3}>Invoices</Heading>
          <DataTable
            columns={[
              {
                property: 'customer',
                header: <Text>Customer</Text>
              },
              {
                property: 'vat_number',
                header: <Text>Vat Number</Text>
              },
              {
                property: 'document_number',
                header: <Text>Doc. Number</Text>
              },
              {
                property: 'type',
                header: <Text>Type</Text>,
                render: ({type}) => invoiceTypes[type] ?? type
              },
              {
                property: 'parent_document',
                header: <Text>Parent Document</Text>
              },
              {
                property: 'total',
                header: <Box align="end">Total</Box>,
                render: ({total, currency}) => <Box align="end">{total} {currency}</Box>
              }
            ]}
            data={invoices}
          />
        </Box>
      }
      {
        total.length > 0 &&
        <Box
          width={{default: '100%', max: '500px'}}
          direction="column"
          pad="medium"
        >
          <Heading level={3}>Total</Heading>
          <DataTable
            columns={[
              {
                property: 'customer',
                header: <Text>Customer</Text>
              },
              {
                property: 'total',
                header: <Box align="end">Total</Box>,
                render: ({total}) => <Box align="end">{total}</Box>
              }
            ]}
            data={total}
          />
        </Box>
      }
    </Grommet>
  )
}
