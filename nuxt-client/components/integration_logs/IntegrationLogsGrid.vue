<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Log Details
        </v-card-title>
        <span v-if="loading" class="ml-5 k-i-loading"/>
        <v-btn v-if="showRefreshGrid" small color="info" style="float: right" class="my-4 mr-3" @click="getDataFromApi">Refresh</v-btn>
      </v-col>
    </v-row>

    <Grid
      class="compact-grid"
      v-if="showGrid"
      :height="500"
      :kGridData="gridData"
      :kSchema="gridSchema"
      :kColumns="gridColumns"
      :kGroup="gridGroup"
      :kAggregate="gridAggregate"
      :kPageSize="100"
    />

  </v-card>
</template>

<script>

import Grid from "~/components/kendo/grid2/Grid"

export default {
  name: "IntegrationRunsGrid",
  props: ['value', 'integrationRun'],
  components: {
    Grid
  },
  data: () => ({
    showRefreshGrid: true,
    loading: false,
    showGrid: false,
    gridData: [],
    gridColumns: [],
    gridSchema: {},
    gridGroup: null,
    gridAggregate: [],
    kColumnWidthDefault: "300px",
    kColumnWidthMidDefault: "175px",
    kColumnWidthSmallDefault: "75px",
    kDefaultContains: { cell: { operator: 'contains' } }
  }),
  created () {
    this.getDataFromApi()
  },
  methods: {
    async getDataFromApi() {
      this.loading = true
      this.showGrid = false
      const response = await this.$axios.$get('integration_logs', {
        params: {
          filterByIntegrationRun: this.integrationRun.id
        }
      })

      this.gridData = response.data
      this.gridColumns = []
      this.gridColumns.push({field: 'message', title: 'Log Message', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'is_error', title: 'Error', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthSmallDefault})
      this.gridColumns.push({field: 'is_summary', title: 'Summary', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthSmallDefault})
      this.gridColumns.push({field: 'created_at', title: 'Created Date', hidden: true, format: '{0:MM/dd/yyyy HH:mm:ss}', width: this.kColumnWidthMidDefault})
      // this.gridColumns.push({field: 'updated_at', title: 'Last Updated Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      // this.gridColumns.push({field: 'view', title: 'View', hidden: false, width: '100px', template: this.openData('id', null, '/integration_runs', null, 'View') })
      this.gridSchema = {
        model: {
          id: "id",
          fields: {
            message: {type: 'string'},
            is_error: {type: 'boolean'},
            is_summary: {type: 'boolean'},
            created_at: {type: 'date'},
            updated_at: {type: 'date'}
          }
        }
      }
      // this.gridGroup = {
      //   field: 'vendor_name', aggregates: [
      //     {field: 'vendor_name', aggregate: "count"},
      //     {field: 'total_price', aggregate: "sum"}
      //   ]
      // }
      // this.gridAggregate = [
      //   {field: 'vendor_name', aggregate: "count"},
      //   {field: 'total_price', aggregate: "sum"}
      // ]

      this.showGrid = true
      this.loading = false
    },
    refresh () {
      this.showGrid = false
      this.$nextTick().then(() => {
        this.showGrid = true
      })
    }
  }
}
</script>

<style scoped>

</style>
