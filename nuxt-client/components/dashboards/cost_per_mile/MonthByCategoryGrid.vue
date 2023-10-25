<template>
  <KGrid
    class="compact-grid"
    v-if="gridDataLoaded"
    :height="700"
    :kGridData="gridData"
    :kSchema="gridSchema"
    :kColumns="kGridColumns"
    :kAggregate="kGridAggregates"
    :kToolBar="['excel', 'pdf']"
    :kPageSize="100"
    :kExportFileName="kExportFileName"
    @grid-filtered-update="gridFilteredUpdate"
    @force_refresh_grid="refreshGrid"
  />
</template>

<script>

import KGrid from "~/components/kendo/grid2/Grid"

export default {
  name: "CPMMonthByCategoryGrid",
  props: ['filteredClientId','filteredBranchId','filteredFleetId','startDate','endDate'],
  components: {
    KGrid,
  },
  data: () => ({
    gridDataLoaded: false,
    gridData: [],
    gridSchema: {},
    summaryTypes: null,
    kGridColumns: [],
    kColumnWidthDefault: "225px",
    kDefaultContains: {cell: {operator: 'contains'}},
    kGridAggregates: [],
    kExportFileName: null,
    dynamicColumnsAdded: false,
  }),
  async created () {
    this.gridDataLoaded = false
    this.kGridColumns = [
      { field: 'month', title: 'Month', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault },
      //{ field: 'client_name', title: 'Account', hidden: false,filterable: this.kDefaultContains, width: this.kColumnWidthDefault },
      { field: 'start_meter', title: 'Start Meter', hidden: true, format: "{0:n0}", width: this.kColumnWidthDefault},
      { field: 'end_meter', title: 'End Meter', hidden: true, format: "{0:n0}", width: this.kColumnWidthDefault},
      { field: 'distance', title: 'Distance', format: "{0:n0}", hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n0')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n0')#"}
    ]
    this.kGridAggregates.push({ field: 'distance', aggregate: 'sum' })

    this.gridSchema = {
      model: {
        id: "id",
        fields: {
          month: {type: 'string'},
          client_name: {type: 'string'},
          start_meter: {type: 'number'},
          end_meter: {type: 'number'},
          distance: {type: 'number'},
          contributors: {type: 'number'},
          fuel: {type: 'number'},
          total_cost: {type: 'number'},
          cpm_fuel: {type: 'number'},
          cpm_wo_fuel: {type: 'number'},
          fuel_total_units: {type: 'number'},
          distance_per_unit: {type: 'number'},
        }
      }
    }

    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.gridDataLoaded = false
      this.gridData = []

      const reportStartDate = new Date(this.startDate + 'T00:00:00Z') //convert yyyy-mm-dd string into timzone specific dates
      const reportEndDate = new Date(this.endDate + 'T23:59:59Z') //convert yyyy-mm-dd string into timzone specific dates

      const response = await this.$axios.$get('/reports/cost_per_mile_summary', {
        params: {
          _sort:  null,
          _sort_dir: 'desc',
          filterByClient: (this.filteredClientId && this.filteredClientId != 'all') ? this.filteredClientId : null,
          filterByBranch: (this.filteredBranchId && this.filteredBranchId != 'all') ? this.filteredBranchId : null,
          filterByFleet: (this.filteredFleetId && this.filteredFleetId != 'all') ? this.filteredFleetId : null,
          startDate: reportStartDate,
          endDate: reportEndDate,
        }
      })

      this.gridData = response.data
      this.summaryTypes = response.summaryTypes
      if(!this.dynamicColumnsAdded) {
        for (let key in this.summaryTypes) {
          let column = this.summaryTypes[key]
          let title = column.charAt(0).toUpperCase() + column.slice(1)
          this.kGridColumns.push({
            field: column,
            title: title,
            hidden: false,
            format: "{0:c}",
            width: this.kColumnWidthDefault,
            aggregates: ['sum'],
            groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#",
            footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"
          })
          this.kGridAggregates.push({ field: column, aggregate: 'sum' })
          this.gridSchema.model.fields[column] = {type: 'number'}
        }
        this.kGridColumns.push({field: 'contributors', title: 'Contributors', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"})
        this.kGridColumns.push({field: 'fuel', title: 'Fuel', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"})
        this.kGridColumns.push({field: 'total_cost', title: 'Total Cost', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"})
        this.kGridColumns.push({field: 'cpm_fuel', title: 'CPM w/ Fuel', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ["average"], groupFooterTemplate: "#=data.value# (#= average#)", footerTemplate: "Average: #=kendo.toString(kendo.parseFloat(average), 'n')#"})
        this.kGridColumns.push({field: 'cpm_wo_fuel', title: 'CPM w/o Fuel', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ["average"], groupFooterTemplate: "#=data.value# (#= average#)", footerTemplate: "Average: #=kendo.toString(kendo.parseFloat(average), 'n')#"})
        this.kGridColumns.push({field: 'fuel_total_units', title: 'Gallons', hidden: false, format: "{0:n}", width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n')#"})
        this.kGridColumns.push({field: 'distance_per_unit', title: 'MPG', hidden: false, format: "{0:n}", width: this.kColumnWidthDefault, aggregates: ["average"], groupFooterTemplate: "#=data.value# (#= average#)", footerTemplate: "Average: #=kendo.toString(kendo.parseFloat(average), 'n')#"})
        this.kGridAggregates.push({ field: 'contributors', aggregate: 'sum' })
        this.kGridAggregates.push({ field: 'fuel', aggregate: 'sum' })
        this.kGridAggregates.push({ field: 'total_cost', aggregate: 'sum' })
        this.kGridAggregates.push({ field: 'cpm_fuel', aggregate: 'average' })
        this.kGridAggregates.push({ field: 'cpm_wo_fuel', aggregate: 'average' })
        this.kGridAggregates.push({ field: 'fuel_total_units', aggregate: 'sum' })
        this.kGridAggregates.push({ field: 'distance_per_unit', aggregate: 'average' })

        this.dynamicColumnsAdded = true
      }
      for (let key in this.kGridColumns) {
        if (this.kGridColumns[key]['field'] == 'client_name') {
          if (this.filteredClient == null)
            this.kGridColumns[key]['hidden'] = false
          else
            this.kGridColumns[key]['hidden'] = true
          break
        }
      }
      if (this.filteredClient == null) {
        this.kExportFileName = "CPM Summary Report"
      }
      else {
        this.kExportFileName = "CPM Summary Report " + this.filteredClient.name
      }

      this.$emit('grid-data-loaded', this.gridData, this.summaryTypes)
      this.gridDataLoaded = true
    },
    gridFilteredUpdate (filteredData) {
      this.$emit('grid-filtered-update', filteredData)
    },
    refreshGrid () {
      this.getDataFromApi()
    },
  }
}
</script>

<style scoped>

</style>
