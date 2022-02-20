import {Box, DataTable, Heading, Text} from 'grommet'
import React from 'react'

/**
 * show total data formatted in datatable
 * @param data
 * @returns {JSX.Element}
 */
export default ({data}) => (
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
      data={data}
    />
  </Box>
)
