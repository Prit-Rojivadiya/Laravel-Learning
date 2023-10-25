<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          {{integration.name}} Integration Run History
          <span v-if="loading" class="ml-5 k-i-loading"/>
        </v-card-title>
      </v-col>
      <v-col cols="12">
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
  props: ['value', 'integration'],
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
    kColumnWidthDefault: "150px",
    kColumnWidthSmallDefault: "150px",
    kDefaultContains: { cell: { operator: 'contains' } }
  }),
  created () {
    this.getDataFromApi()
  },
  methods: {
    async getDataFromApi() {
      this.loading = true
      this.showGrid = false
      const response = await this.$axios.$get('integration_runs', {
        params: {
          filterByIntegration: this.integration.id,
          filterByTask: null
        }
      })

      this.gridData = response.data
      this.gridColumns = []
      this.gridColumns.push({field: 'task', title: 'Task', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('id', 'task', '/integration_runs', null, null) })
      this.gridColumns.push({field: 'status', title: 'Status', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'started', title: 'Started At', hidden: false, format: '{0:MM/dd/yyyy HH:mm:ss}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'completed', title: 'Completed At', hidden: false, format: '{0:MM/dd/yyyy HH:mm:ss}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'total', title: 'Total Items', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthSmallDefault})
      this.gridColumns.push({field: 'success_count', title: 'Successful', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthSmallDefault})
      this.gridColumns.push({field: 'failed_count', title: 'Failed', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthSmallDefault})
      this.gridColumns.push({field: 'summary_msg', title: 'Summary', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'error_msg', title: 'Errors', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      // this.gridColumns.push({field: 'results', title: 'Failed', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'created_at', title: 'Created Date', hidden: true, format: '{0:MM/dd/yyyy HH:mm:ss}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'updated_at', title: 'Last Updated Date', hidden: true, format: '{0:MM/dd/yyyy HH:mm:ss}', width: this.kColumnWidthDefault})
      // this.gridColumns.push({field: 'view', title: 'View', hidden: false, width: '100px', template: this.openData('id', null, '/integration_runs', null, 'View') })
      this.gridSchema = {
        model: {
          id: "id",
          fields: {
            integration_name: {type: 'string'},
            task: {type: 'string'},
            status: {type: 'string'},
            started: {type: 'date'},
            completed: {type: 'date'},
            total: {type: 'number'},
            success_count: {type: 'number'},
            failed_count: {type: 'number'},
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
    openData (fieldKey, fieldKeyForLabel, link, sclass, staticLabel) {
      if (!sclass) {
        sclass = ''
      }
      if (staticLabel) {
        return `# if(typeof data.${fieldKey} != 'undefined') { # <a class="${sclass}" href="${link}/#=data.${fieldKey}#">${staticLabel}</a> # } #`
      }
      else {
        return `# if(typeof data.${fieldKey} != 'undefined' && data.${fieldKey} != null) { # <a class="${sclass}" href="${link}/#=data.${fieldKey}#">#=(data.${fieldKeyForLabel}).match(/[A-Z][a-z]+|[0-9]+/g).join(" ")#</a> # } #`
      }
      //this.$router.push(`/integration_runs/${dataItem.id}`)
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
