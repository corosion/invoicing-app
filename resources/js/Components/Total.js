import {Box, DataTable, Heading, Text} from 'grommet'
import React from 'react'

export default (totals) => {
  return (
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
        data={totals}
      />
    </Box>
  )
}
