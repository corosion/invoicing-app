import {Box, DataTable, Heading, Text} from 'grommet'
import React from 'react'

export default (data, types) => {
  return (
    <Box
      width={{default: '100%', max: '800px'}}
      direction="column"
      pad="medium"
    >
      <Heading level={3}>Document Preview</Heading>
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
            render: ({type}) => types[type] ?? type
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
        data={data}
      />
    </Box>
  )
}
