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
  name: "CPMUnitByMonthGrid",
  props: ['unitType', 'filteredClientId','filteredBranchId','filteredFleetId','startDate','endDate'],
  components: {
    KGrid,
  },
  data: () => ({
    gridDataLoaded: false,
    gridData: [],
    gridSchema: {},
    monthColumns: [],
    kGridColumns: [],
    kColumnWidthDefault: "225px",
    kDefaultContains: {cell: {operator: 'contains'}},
    kGridAggregates: [],
    kExportFileName: null,
    dynamicColumnsAdded: false,
  }),
  async created () {
    this.gridDataLoaded = false
    this.kGridColumns = []
    this.kExportFileName = 'CPM Detailed Report ' + this.unitType + ' by month'
    if (this.unitType == 'branch') {
      this.kGridColumns.push({ field: 'client_name', title: 'Account', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') })
      this.kGridColumns.push({ field: 'unit', title: 'Branch', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') })
    }
    else if (this.unitType == 'fleet') {
      this.kGridColumns.push({ field: 'client_name', title: 'Account', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') })
      this.kGridColumns.push({ field: 'branch_name', title: 'Branch', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') })
      this.kGridColumns.push({ field: 'unit', title: 'Fleet', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('fleet_id', 'fleet_name', '/fleets', 'tz-grid-hlink') })
    }
    else if (this.unitType == 'vehicle') {
      this.kGridColumns.push({ field: 'client_name', title: 'Account', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') })
      this.kGridColumns.push({ field: 'branch_name', title: 'Branch', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') })
      this.kGridColumns.push({ field: 'fleet_name', title: 'Fleet', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('fleet_id', 'fleet_name', '/fleets', 'tz-grid-hlink') })
      this.kGridColumns.push({ field: 'unit', title: 'Vehicle Unit #', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('vehicle_id', 'vehicle_number', '/vehicles', 'tz-grid-hlink') })
      this.kGridColumns.push({ field: 'start_meter', title: 'Starting Meter',format: "{0:n}", hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault },)
      this.kGridColumns.push({ field: 'end_meter', title: 'Ending Meter', format: "{0:n}", hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault },)
    }
    this.kGridColumns.push({ field: 'distance', title: 'Distance Traveled', format: "{0:n0}", hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n0')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n0')#"})
    this.kGridAggregates.push({ field: 'distance', aggregate: 'sum' })

    this.gridSchema = {
      model: {
        id: "id",
        fields: {
          client_name: {type: 'string'},
          branch_name: {type: 'string'},
          fleet_name: {type: 'string'},
          unit: {type: 'string'},
          start_meter: {type: 'number'},
          end_meter: {type: 'number'},
          distance: {type: 'number'},
          total_cost: {type: 'number'},
          cpm_fuel: {type: 'number'},
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

      const response = await this.$axios.$get('/reports/cost_per_mile_detailed', {
        params: {
          _sort:  null,
          _sort_dir: 'desc',
          reportType: 'month',
          unitType: this.unitType,
          filterByClient: (this.filteredClientId && this.filteredClientId != 'all') ? this.filteredClientId : null,
          filterByBranch: (this.filteredBranchId && this.filteredBranchId != 'all') ? this.filteredBranchId : null,
          filterByFleet: (this.filteredFleetId && this.filteredFleetId != 'all') ? this.filteredFleetId : null,
          startDate: reportStartDate,
          endDate: reportEndDate,
        }
      })

      this.gridData = response.data
      this.monthColumns = response.monthColumns
      if(!this.dynamicColumnsAdded) {
        for (let key in this.monthColumns) {
          let column = this.monthColumns[key]
          let title = column.replaceAll("_"," ")
          //let title = column.charAt(0).toUpperCase() + column.slice(1)
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
          this.kGridAggregates.push({field: column, aggregate: 'sum'})
          this.gridSchema.model.fields[column] = {type: 'number'}
        }
      }
      this.kGridColumns.push({ field: 'total_cost', title: 'Total', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"})
      this.kGridColumns.push({ field: 'cpm_fuel', title: 'CPM w/ Fuel', hidden: false, format: "{0:n}", width: this.kColumnWidthDefault, aggregates: ["average"], groupFooterTemplate: "#=data.value# (#= average#)", footerTemplate: "Average: #=kendo.toString(kendo.parseFloat(average), 'n')#"})
      this.kGridAggregates.push({ field: 'total_cost', aggregate: 'sum' })
      this.kGridAggregates.push({ field: 'cpm_fuel', aggregate: 'average' })

      this.$emit('grid-data-loaded', this.gridData, this.monthColumns)
      this.gridDataLoaded = true
    },
    gridFilteredUpdate (filteredData) {
      this.$emit('grid-filtered-update', filteredData)
    },
    refreshGrid () {
      this.getDataFromApi()
    },
    openData (fieldKey, fieldKeyForLabel, link, sclass, staticLabel) {
      if (!sclass) {
        sclass = ''
      }
      if (staticLabel) {
        return `# if(typeof data.${fieldKey} != 'undefined') { # <a class="${sclass}" href="${link}/#=data.${fieldKey}#">${staticLabel}</a> # } #`
      }
      else {
        return `# if(typeof data.${fieldKey} != 'undefined' && data.${fieldKey} != null) { # <a class="${sclass}" href="${link}/#=data.${fieldKey}#">#=data.${fieldKeyForLabel}#</a> # } #`
      }
    }
  }
}
</script>

<style scoped>

</style>
